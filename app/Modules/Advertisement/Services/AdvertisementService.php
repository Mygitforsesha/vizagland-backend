<?php

namespace App\Modules\Advertisement\Services;

use App\Modules\Advertisement\Models\Advertisement;
use App\Modules\Advertisement\Repositories\AdvertisementRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdvertisementService
{
    public function __construct(
        private readonly AdvertisementRepository $advertisementRepository,
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
     */
    public function create(array $attributes, UploadedFile $image, User $user): Advertisement
    {
        $uploadedPath = null;

        try {
            return DB::transaction(function () use ($attributes, $image, $user, &$uploadedPath) {
                $uploadedPath = $image->store('advertisements', 'public');

                return $this->advertisementRepository->create([
                    ...$attributes,
                    'advertisement_image_path' => $uploadedPath,
                    'advertisement_created_by_user_id' => $user->user_id,
                ]);
            });
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                Storage::disk('public')->delete($uploadedPath);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $advertisementId, array $attributes, ?UploadedFile $image): Advertisement
    {
        $advertisement = $this->show($advertisementId);
        $uploadedPath = null;
        $previousPath = $advertisement->advertisement_image_path;

        try {
            return DB::transaction(function () use ($advertisement, $attributes, $image, &$uploadedPath, $previousPath) {
                if ($image !== null) {
                    $uploadedPath = $image->store('advertisements', 'public');
                    $attributes['advertisement_image_path'] = $uploadedPath;
                }

                $updatedAdvertisement = $this->advertisementRepository->update($advertisement, $attributes);

                if ($uploadedPath !== null && $previousPath !== '') {
                    Storage::disk('public')->delete($previousPath);
                }

                return $updatedAdvertisement;
            });
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                Storage::disk('public')->delete($uploadedPath);
            }

            throw $exception;
        }
    }

    public function delete(int $advertisementId): void
    {
        $advertisement = $this->show($advertisementId);
        $imagePath = $advertisement->advertisement_image_path;

        DB::transaction(function () use ($advertisement): void {
            $this->advertisementRepository->delete($advertisement);
        });

        if ($imagePath !== '') {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
