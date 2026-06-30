<?php

namespace App\Modules\Property\Services;

use App\Modules\Property\Models\PropertyDocument;
use App\Modules\Property\Models\PropertyImage;
use App\Modules\Property\Repositories\PropertyMediaRepository;
use App\Modules\Property\Repositories\PropertyRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PropertyMediaService
{
    private const MAX_IMAGES = 30;

    private const MAX_DOCUMENTS = 30;

    public function __construct(
        private readonly PropertyRepository $propertyRepository,
        private readonly PropertyMediaRepository $propertyMediaRepository,
        private readonly PropertyMediaStorage $propertyMediaStorage,
    ) {}

    public function uploadImage(int $propertyId, UploadedFile $image): PropertyImage
    {
        $uploadedPath = null;

        try {
            return DB::transaction(function () use ($propertyId, $image, &$uploadedPath) {
                $this->ensurePropertyExists($propertyId);

                $currentCount = $this->propertyMediaRepository->countImages($propertyId);

                if ($currentCount >= self::MAX_IMAGES) {
                    throw new RuntimeException('Maximum of '.self::MAX_IMAGES.' images allowed per property.');
                }

                $uploadedPath = $this->propertyMediaStorage->storeImage($image);

                return $this->propertyMediaRepository->createImage($propertyId, [
                    'property_image_path' => $uploadedPath,
                    'property_image_original_name' => $image->getClientOriginalName(),
                    'property_image_size' => $image->getSize(),
                    'property_image_mime_type' => $image->getMimeType(),
                    'property_image_sort_order' => $this->propertyMediaRepository->getNextImageSortOrder($propertyId),
                ]);
            });
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                $this->propertyMediaStorage->delete($uploadedPath);
            }

            throw $exception;
        }
    }

    public function uploadDocument(int $propertyId, UploadedFile $document): PropertyDocument
    {
        $uploadedPath = null;

        try {
            return DB::transaction(function () use ($propertyId, $document, &$uploadedPath) {
                $this->ensurePropertyExists($propertyId);

                $currentCount = $this->propertyMediaRepository->countDocuments($propertyId);

                if ($currentCount >= self::MAX_DOCUMENTS) {
                    throw new RuntimeException('Maximum of '.self::MAX_DOCUMENTS.' documents allowed per property.');
                }

                $uploadedPath = $this->propertyMediaStorage->storeDocument($document);

                return $this->propertyMediaRepository->createDocument($propertyId, [
                    'property_document_original_name' => $document->getClientOriginalName(),
                    'property_document_mime_type' => $document->getMimeType(),
                    'property_document_path' => $uploadedPath,
                    'property_document_size' => $document->getSize(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                $this->propertyMediaStorage->delete($uploadedPath);
            }

            throw $exception;
        }
    }

    public function deleteImage(int $propertyImageId): void
    {
        DB::transaction(function () use ($propertyImageId) {
            $image = $this->propertyMediaRepository->findImageById($propertyImageId);

            if ($image === null) {
                throw (new ModelNotFoundException)->setModel(PropertyImage::class, [$propertyImageId]);
            }

            $property = $this->propertyRepository->findById($image->property_id);

            if ($property?->isOriginal()) {
                throw new RuntimeException('Original property records cannot be modified.');
            }

            $path = $image->property_image_path;
            $this->propertyMediaRepository->deleteImage($image);

            $this->propertyMediaStorage->delete($path);
        });
    }

    public function deleteDocument(int $propertyDocumentId): void
    {
        DB::transaction(function () use ($propertyDocumentId) {
            $document = $this->propertyMediaRepository->findDocumentById($propertyDocumentId);

            if ($document === null) {
                throw (new ModelNotFoundException)->setModel(PropertyDocument::class, [$propertyDocumentId]);
            }

            $property = $this->propertyRepository->findById($document->property_id);

            if ($property?->isOriginal()) {
                throw new RuntimeException('Original property records cannot be modified.');
            }

            $path = $document->property_document_path;
            $this->propertyMediaRepository->deleteDocument($document);

            $this->propertyMediaStorage->delete($path);
        });
    }

    private function ensurePropertyExists(int $propertyId): void
    {
        $property = $this->propertyRepository->findById($propertyId);

        if ($property === null) {
            throw (new ModelNotFoundException)->setModel(
                \App\Modules\Property\Models\Property::class,
                [$propertyId],
            );
        }

        if ($property->isOriginal()) {
            throw new RuntimeException('Original property records cannot be modified.');
        }
    }
}
