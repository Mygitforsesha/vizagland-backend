<?php

namespace App\Modules\Advertisement\Services;

use App\Modules\Advertisement\Models\Advertisement;
use App\Modules\Advertisement\Models\AdvertisementDocument;
use App\Modules\Advertisement\Models\AdvertisementImage;
use App\Modules\Advertisement\Repositories\AdvertisementRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdvertisementService
{
    public function __construct(
        private readonly AdvertisementRepository $advertisementRepository,
        private readonly AdvertisementMediaStorage $advertisementMediaStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listAdmin(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->advertisementRepository->paginateAdmin($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Advertisement>
     */
    public function listPublic(array $filters): Collection
    {
        return $this->advertisementRepository->listActivePublic($filters);
    }

    /**
     * @return array{village_wise_ads: Collection<int, Advertisement>, general_ads: Collection<int, Advertisement>, latest_ads: Collection<int, Advertisement>}
     */
    public function listPublicSections(): array
    {
        return $this->advertisementRepository->getPublicSections();
    }

    public function showPublic(int $advertisementId): Advertisement
    {
        $advertisement = $this->advertisementRepository->findActivePublicById($advertisementId);

        if ($advertisement === null) {
            throw (new ModelNotFoundException)->setModel(Advertisement::class, [$advertisementId]);
        }

        return $advertisement;
    }

    public function show(int $advertisementId): Advertisement
    {
        $advertisement = $this->advertisementRepository->findById($advertisementId);

        if ($advertisement === null) {
            throw (new ModelNotFoundException)->setModel(Advertisement::class, [$advertisementId]);
        }

        return $advertisement;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     */
    public function create(array $attributes, array $images, array $documents, User $user): Advertisement
    {
        $uploadedPaths = [];

        try {
            return DB::transaction(function () use ($attributes, $images, $documents, $user, &$uploadedPaths) {
                $imageRows = $this->storeImageFiles($images, $uploadedPaths);
                $documentRows = $this->storeDocumentFiles($documents, $uploadedPaths);

                $advertisement = $this->advertisementRepository->create([
                    ...$attributes,
                    'advertisement_image_path' => $imageRows[0]['advertisement_image_path'],
                    'advertisement_created_by_user_id' => $user->user_id,
                ]);

                $this->insertImages($advertisement, $imageRows);
                $this->insertDocuments($advertisement, $documentRows);

                return $advertisement->fresh(['village', 'images', 'documents']);
            });
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($uploadedPaths);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     */
    public function update(int $advertisementId, array $attributes, array $images, array $documents): Advertisement
    {
        $advertisement = $this->show($advertisementId);
        $uploadedPaths = [];

        try {
            return DB::transaction(function () use ($advertisement, $attributes, $images, $documents, &$uploadedPaths) {
                $previousImagePaths = [];
                $previousDocumentPaths = [];

                if ($images !== []) {
                    $previousImagePaths = $this->existingImagePaths($advertisement);
                    $imageRows = $this->storeImageFiles($images, $uploadedPaths);
                    $attributes['advertisement_image_path'] = $imageRows[0]['advertisement_image_path'];
                    $advertisement->images()->delete();
                    $this->insertImages($advertisement, $imageRows);
                }

                if ($documents !== []) {
                    $previousDocumentPaths = $this->existingDocumentPaths($advertisement);
                    $documentRows = $this->storeDocumentFiles($documents, $uploadedPaths);
                    $advertisement->documents()->delete();
                    $this->insertDocuments($advertisement, $documentRows);
                }

                $updatedAdvertisement = $this->advertisementRepository->update($advertisement, $attributes);

                $this->deleteStoredPaths([...$previousImagePaths, ...$previousDocumentPaths]);

                return $updatedAdvertisement->fresh(['village', 'images', 'documents']);
            });
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($uploadedPaths);

            throw $exception;
        }
    }

    public function delete(int $advertisementId): void
    {
        $advertisement = $this->show($advertisementId);
        $advertisement->load(['images', 'documents']);
        $paths = array_values(array_filter([
            $advertisement->advertisement_image_path,
            ...$advertisement->images->pluck('advertisement_image_path')->all(),
            ...$advertisement->documents->pluck('advertisement_document_path')->all(),
        ]));

        DB::transaction(function () use ($advertisement): void {
            $this->advertisementRepository->delete($advertisement);
        });

        $this->deleteStoredPaths($paths);
    }

    /**
     * @param  list<UploadedFile>  $images
     * @param  list<string>  $uploadedPaths
     * @return list<array{advertisement_image_path: string, advertisement_image_original_name: string, advertisement_image_mime_type: ?string, advertisement_image_size: int|false, advertisement_image_sort_order: int}>
     */
    private function storeImageFiles(array $images, array &$uploadedPaths): array
    {
        $rows = [];

        foreach ($images as $index => $image) {
            $path = $this->advertisementMediaStorage->storeImage($image);
            $uploadedPaths[] = $path;
            $rows[] = [
                'advertisement_image_path' => $path,
                'advertisement_image_original_name' => $image->getClientOriginalName(),
                'advertisement_image_mime_type' => $image->getMimeType(),
                'advertisement_image_size' => $image->getSize(),
                'advertisement_image_sort_order' => $index,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<UploadedFile>  $documents
     * @param  list<string>  $uploadedPaths
     * @return list<array{advertisement_document_path: string, advertisement_document_original_name: string, advertisement_document_mime_type: ?string, advertisement_document_size: int|false}>
     */
    private function storeDocumentFiles(array $documents, array &$uploadedPaths): array
    {
        $rows = [];

        foreach ($documents as $document) {
            $path = $this->advertisementMediaStorage->storeDocument($document);
            $uploadedPaths[] = $path;
            $rows[] = [
                'advertisement_document_path' => $path,
                'advertisement_document_original_name' => $document->getClientOriginalName(),
                'advertisement_document_mime_type' => $document->getMimeType(),
                'advertisement_document_size' => $document->getSize(),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function insertImages(Advertisement $advertisement, array $rows): void
    {
        foreach ($rows as $row) {
            AdvertisementImage::query()->create([
                'advertisement_id' => $advertisement->advertisement_id,
                ...$row,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function insertDocuments(Advertisement $advertisement, array $rows): void
    {
        foreach ($rows as $row) {
            AdvertisementDocument::query()->create([
                'advertisement_id' => $advertisement->advertisement_id,
                ...$row,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function existingImagePaths(Advertisement $advertisement): array
    {
        $advertisement->loadMissing('images');

        return array_values(array_filter([
            $advertisement->advertisement_image_path,
            ...$advertisement->images->pluck('advertisement_image_path')->all(),
        ]));
    }

    /**
     * @return list<string>
     */
    private function existingDocumentPaths(Advertisement $advertisement): array
    {
        $advertisement->loadMissing('documents');

        return $advertisement->documents->pluck('advertisement_document_path')->filter()->values()->all();
    }

    /**
     * @param  list<string>  $paths
     */
    private function deleteStoredPaths(array $paths): void
    {
        foreach (array_unique($paths) as $path) {
            $this->advertisementMediaStorage->delete($path);
        }
    }
}
