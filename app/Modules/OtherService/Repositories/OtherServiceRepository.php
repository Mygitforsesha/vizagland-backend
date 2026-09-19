<?php

namespace App\Modules\OtherService\Repositories;

use App\Modules\OtherService\Models\OtherService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OtherServiceRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAdmin(array $filters, int $perPage, string $sortBy, string $sortDirection): LengthAwarePaginator
    {
        $query = OtherService::query();

        $this->applyAdminFilters($query, $filters);

        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'other_service_id') {
            $query->orderByDesc('other_service_id');
        }

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, OtherService>
     */
    public function listActivePublic(): Collection
    {
        return OtherService::query()
            ->where('other_service_is_active', true)
            ->orderBy('other_service_sort_order')
            ->orderBy('other_service_name')
            ->get();
    }

    public function findById(int $otherServiceId): ?OtherService
    {
        return OtherService::query()->find($otherServiceId);
    }

    public function findBySlug(string $slug): ?OtherService
    {
        return OtherService::query()
            ->where('other_service_slug', $slug)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): OtherService
    {
        return OtherService::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(OtherService $otherService, array $attributes): OtherService
    {
        $otherService->update($attributes);

        return $otherService->fresh();
    }

    public function deactivate(OtherService $otherService): OtherService
    {
        $otherService->update(['other_service_is_active' => false]);

        return $otherService->fresh();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = OtherService::query()->where('other_service_slug', $slug);

        if ($ignoreId !== null) {
            $query->where('other_service_id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * @param  Builder<OtherService>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyAdminFilters(Builder $query, array $filters): void
    {
        if (array_key_exists('other_service_is_active', $filters) && $filters['other_service_is_active'] !== null) {
            $query->where(
                'other_service_is_active',
                filter_var($filters['other_service_is_active'], FILTER_VALIDATE_BOOLEAN),
            );
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('other_service_name', 'like', '%'.$search.'%')
                    ->orWhere('other_service_slug', 'like', '%'.$search.'%');
            });
        }
    }
}
