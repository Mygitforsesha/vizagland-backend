<?php

namespace App\Modules\OtherService\Repositories;

use App\Modules\OtherService\Models\OtherService;
use App\Modules\OtherService\Models\OtherServiceWhatsappNumber;
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
        $query = OtherService::query()->with('whatsappNumbers');

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
            ->with('whatsappNumbers')
            ->where('other_service_is_active', true)
            ->orderBy('other_service_sort_order')
            ->orderBy('other_service_name')
            ->get();
    }

    public function findById(int $otherServiceId): ?OtherService
    {
        return OtherService::query()->with('whatsappNumbers')->find($otherServiceId);
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
        return OtherService::query()->create($attributes)->fresh('whatsappNumbers');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(OtherService $otherService, array $attributes): OtherService
    {
        $otherService->update($attributes);

        return $otherService->fresh('whatsappNumbers');
    }

    public function deactivate(OtherService $otherService): OtherService
    {
        $otherService->update(['other_service_is_active' => false]);

        return $otherService->fresh('whatsappNumbers');
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
     * @param  list<array{other_service_whatsapp_number: string, other_service_whatsapp_number_is_primary: bool, other_service_whatsapp_number_sort_order: int}>  $numbers
     */
    public function syncWhatsappNumbers(OtherService $otherService, array $numbers): void
    {
        $otherService->whatsappNumbers()->delete();

        foreach ($numbers as $number) {
            OtherServiceWhatsappNumber::query()->create([
                'other_service_id' => $otherService->other_service_id,
                ...$number,
            ]);
        }
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
