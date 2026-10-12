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
            ->with($this->detailRelations())
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
        $query = $this->activePublicQuery()
            ->with($this->detailRelations())
            ->orderBy('advertisement_display_order')
            ->orderByDesc('advertisement_id');

        if (! empty($filters['advertisement_type'])) {
            $query->where('advertisement_type', $filters['advertisement_type']);
        }

        return $query->get();
    }

    /**
     * @return array{village_wise_ads: Collection<int, Advertisement>, general_ads: Collection<int, Advertisement>, latest_ads: Collection<int, Advertisement>}
     */
    public function getPublicSections(): array
    {
        $villageWiseAds = $this->activePublicQuery()
            ->where(function (Builder $builder): void {
                $builder->where('advertisement_category', 'village_wise')
                    ->orWhere('advertisement_category', 'village_wise_ads')
                    ->orWhere('advertisement_category', 'Village Wise Ads')
                    ->orWhere('advertisement_type', 'like', '%village_wise%')
                    ->orWhereJsonContains('advertisement_types', 'village_wise_ads')
                    ->orWhereJsonContains('advertisement_types', 'village_wise');
            })
            ->whereNotNull('advertisement_village_id')
            ->with($this->detailRelations())
            ->orderBy('advertisement_display_order')
            ->orderByDesc('advertisement_id')
            ->get();

        $generalAds = $this->activePublicQuery()
            ->where(function (Builder $builder): void {
                $builder->where('advertisement_category', 'general')
                    ->orWhere('advertisement_category', 'general_ads')
                    ->orWhere('advertisement_category', 'General Ads')
                    ->orWhere('advertisement_type', 'like', '%general%')
                    ->orWhereJsonContains('advertisement_types', 'general_ads')
                    ->orWhereJsonContains('advertisement_types', 'general')
                    ->orWhereNull('advertisement_category');
            })
            ->with($this->detailRelations())
            ->orderBy('advertisement_display_order')
            ->orderByDesc('advertisement_id')
            ->get();

        $latestAds = $this->activePublicQuery()
            ->where(function (Builder $builder): void {
                $builder->where('advertisement_category', 'latest')
                    ->orWhere('advertisement_category', 'latest_ads')
                    ->orWhere('advertisement_category', 'Latest Ads')
                    ->orWhere('advertisement_type', 'like', '%latest%')
                    ->orWhereJsonContains('advertisement_types', 'latest_ads')
                    ->orWhereJsonContains('advertisement_types', 'latest');
            })
            ->with($this->detailRelations())
            ->orderByDesc('advertisement_id')
            ->get();

        return [
            'village_wise_ads' => $villageWiseAds,
            'general_ads' => $generalAds,
            'latest_ads' => $latestAds,
        ];
    }

    public function findActivePublicById(int $advertisementId): ?Advertisement
    {
        return $this->activePublicQuery()
            ->where('advertisement_id', $advertisementId)
            ->with($this->detailRelations())
            ->first();
    }

    public function findById(int $advertisementId): ?Advertisement
    {
        return Advertisement::query()
            ->with($this->detailRelations())
            ->find($advertisementId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Advertisement
    {
        $advertisement = Advertisement::query()->create($attributes);

        return $advertisement->fresh($this->detailRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Advertisement $advertisement, array $attributes): Advertisement
    {
        $advertisement->update($attributes);

        return $advertisement->fresh($this->detailRelations());
    }

    public function delete(Advertisement $advertisement): void
    {
        $advertisement->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function detailRelations(): array
    {
        return [
            'village',
            'images',
            'documents',
        ];
    }

    private function activePublicQuery(): Builder
    {
        return Advertisement::query()
            ->where('advertisement_is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNull('advertisement_start_date')
                    ->orWhereDate('advertisement_start_date', '<=', Carbon::today());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('advertisement_end_date')
                    ->orWhereDate('advertisement_end_date', '>=', Carbon::today());
            });
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
