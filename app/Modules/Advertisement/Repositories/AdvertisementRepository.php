<?php

namespace App\Modules\Advertisement\Repositories;

use App\Modules\Advertisement\Models\Advertisement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AdvertisementRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAdmin(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Advertisement::query()
            ->orderBy('advertisement_display_order')
            ->orderByDesc('advertisement_id');

        $this->applyAdminFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Advertisement>
     */
    public function listActivePublic(array $filters): Collection
    {
        $query = Advertisement::query()
            ->where('advertisement_is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNull('advertisement_start_date')
                    ->orWhereDate('advertisement_start_date', '<=', Carbon::today());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('advertisement_end_date')
                    ->orWhereDate('advertisement_end_date', '>=', Carbon::today());
            })
            ->orderBy('advertisement_display_order')
            ->orderByDesc('advertisement_id');

        if (! empty($filters['advertisement_type'])) {
            $query->where('advertisement_type', $filters['advertisement_type']);
        }

        return $query->get();
    }

    public function findById(int $advertisementId): ?Advertisement
    {
        return Advertisement::query()->find($advertisementId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Advertisement
    {
        return Advertisement::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Advertisement $advertisement, array $attributes): Advertisement
    {
        $advertisement->update($attributes);

        return $advertisement->fresh();
    }

    public function delete(Advertisement $advertisement): void
    {
        $advertisement->delete();
    }

    /**
     * @param  Builder<Advertisement>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyAdminFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['advertisement_type'])) {
            $query->where('advertisement_type', $filters['advertisement_type']);
        }

        if (isset($filters['advertisement_is_active'])) {
            $query->where('advertisement_is_active', $filters['advertisement_is_active']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('advertisement_title', 'like', '%'.$search.'%')
                    ->orWhere('advertisement_description', 'like', '%'.$search.'%');
            });
        }
    }
}
