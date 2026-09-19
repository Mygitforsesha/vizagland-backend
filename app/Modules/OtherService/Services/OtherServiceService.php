<?php

namespace App\Modules\OtherService\Services;

use App\Modules\OtherService\Models\OtherService;
use App\Modules\OtherService\Repositories\OtherServiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class OtherServiceService
{
    public function __construct(
        private readonly OtherServiceRepository $otherServiceRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listAdmin(array $filters, int $perPage, string $sortBy, string $sortDirection): LengthAwarePaginator
    {
        return $this->otherServiceRepository->paginateAdmin($filters, $perPage, $sortBy, $sortDirection);
    }

    /**
     * @return Collection<int, OtherService>
     */
    public function listActivePublic(): Collection
    {
        return $this->otherServiceRepository->listActivePublic();
    }

    public function show(int $otherServiceId): OtherService
    {
        $otherService = $this->otherServiceRepository->findById($otherServiceId);

        if ($otherService === null) {
            throw (new ModelNotFoundException)->setModel(OtherService::class, [$otherServiceId]);
        }

        return $otherService;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): OtherService
    {
        $name = (string) $attributes['other_service_name'];
        $slug = $attributes['other_service_slug'] ?? null;

        if (! is_string($slug) || trim($slug) === '') {
            $slug = Str::slug($name);
        } else {
            $slug = Str::slug($slug);
        }

        if ($slug === '') {
            $slug = 'other-service';
        }

        $attributes['other_service_slug'] = $this->ensureUniqueSlug($slug);
        $attributes['other_service_sort_order'] = (int) ($attributes['other_service_sort_order'] ?? 0);
        $attributes['other_service_is_active'] = (bool) ($attributes['other_service_is_active'] ?? true);

        return $this->otherServiceRepository->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $otherServiceId, array $attributes): OtherService
    {
        $otherService = $this->show($otherServiceId);

        if (array_key_exists('other_service_slug', $attributes)) {
            $slug = $attributes['other_service_slug'];

            if (! is_string($slug) || trim($slug) === '') {
                $name = $attributes['other_service_name'] ?? $otherService->other_service_name;
                $slug = Str::slug((string) $name);
            } else {
                $slug = Str::slug($slug);
            }

            if ($slug === '') {
                $slug = 'other-service';
            }

            $attributes['other_service_slug'] = $this->ensureUniqueSlug($slug, $otherServiceId);
        }

        if (array_key_exists('other_service_sort_order', $attributes)) {
            $attributes['other_service_sort_order'] = (int) $attributes['other_service_sort_order'];
        }

        if (array_key_exists('other_service_is_active', $attributes)) {
            $attributes['other_service_is_active'] = (bool) $attributes['other_service_is_active'];
        }

        return $this->otherServiceRepository->update($otherService, $attributes);
    }

    public function updateStatus(int $otherServiceId, bool $isActive): OtherService
    {
        $otherService = $this->show($otherServiceId);

        return $this->otherServiceRepository->update($otherService, [
            'other_service_is_active' => $isActive,
        ]);
    }

    public function delete(int $otherServiceId): OtherService
    {
        $otherService = $this->show($otherServiceId);

        return $this->otherServiceRepository->deactivate($otherService);
    }

    private function ensureUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->otherServiceRepository->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
