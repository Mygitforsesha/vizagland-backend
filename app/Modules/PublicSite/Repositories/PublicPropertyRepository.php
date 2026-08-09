<?php

namespace App\Modules\PublicSite\Repositories;

use App\Modules\MasterLocation\Models\MasterLocation;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PublicPropertyRepository
{
    public function findApprovedById(int $propertyId): ?Property
    {
        return Property::query()
            ->with([
                'images' => fn ($query) => $query->orderBy('property_image_sort_order'),
            ])
            ->where('property_id', $propertyId)
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_status', PropertyStatus::Approved)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateApproved(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Property::query()
            ->select([
                'property_id',
                'property_title',
                'property_type',
                'property_listing_type',
                'property_price',
                'property_city',
                'property_locality',
                'property_bedrooms',
                'property_ownership_type',
                'property_published_at',
                'created_at',
            ])
            ->with(['images' => fn ($query) => $query->orderBy('property_image_sort_order')->limit(1)])
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_status', PropertyStatus::Approved)
            ->orderByDesc('property_published_at');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Property>
     */
    public function featured(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Property::query()
            ->select([
                'property_id',
                'property_title',
                'property_type',
                'property_listing_type',
                'property_price',
                'property_city',
                'property_locality',
                'property_bedrooms',
                'property_ownership_type',
                'property_published_at',
            ])
            ->with(['images' => fn ($query) => $query->orderBy('property_image_sort_order')->limit(1)])
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_status', PropertyStatus::Approved)
            ->where('property_is_featured', true)
            ->orderByDesc('property_published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * All master villages for the browse-land page, with approved listing counts (0 when none).
     *
     * @return array{villages: list<array{id: int, village: string, mandal: string, district: string, property_count: int}>, mandals: list<string>}
     */
    public function browseAreas(): array
    {
        $propertyCounts = $this->approvedPublicQuery()
            ->whereNotNull('property_village')
            ->where('property_village', '!=', '')
            ->selectRaw('LOWER(property_village) as village_key')
            ->selectRaw('COUNT(*) as property_count')
            ->groupByRaw('LOWER(property_village)')
            ->pluck('property_count', 'village_key');

        $villages = MasterLocation::query()
            ->select([
                DB::raw('MIN(master_location_id) as id'),
                'master_location_village',
                'master_location_mandal',
                'master_location_district',
            ])
            ->whereNotNull('master_location_village')
            ->where('master_location_village', '!=', '')
            ->groupBy('master_location_village', 'master_location_mandal', 'master_location_district')
            ->orderBy('master_location_village')
            ->get()
            ->map(static function ($row) use ($propertyCounts): array {
                $village = (string) $row->master_location_village;

                return [
                    'id' => (int) $row->id,
                    'village' => $village,
                    'mandal' => (string) ($row->master_location_mandal ?? ''),
                    'district' => (string) ($row->master_location_district ?? ''),
                    'property_count' => (int) ($propertyCounts[mb_strtolower($village)] ?? 0),
                ];
            })
            ->values()
            ->all();

        $mandals = MasterLocation::query()
            ->whereNotNull('master_location_mandal')
            ->where('master_location_mandal', '!=', '')
            ->distinct()
            ->orderBy('master_location_mandal')
            ->pluck('master_location_mandal')
            ->map(static fn ($mandal): string => (string) $mandal)
            ->values()
            ->all();

        return [
            'villages' => $villages,
            'mandals' => $mandals,
        ];
    }

    /**
     * @return Builder<Property>
     */
    private function approvedPublicQuery(): Builder
    {
        return Property::query()
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_status', PropertyStatus::Approved);
    }

    /**
     * @param  Builder<Property>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['property_city'])) {
            $query->where('property_city', $filters['property_city']);
        }

        if (! empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (isset($filters['price_min'])) {
            $query->where('property_price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('property_price', '<=', $filters['price_max']);
        }

        if (! empty($filters['bedrooms'])) {
            $query->where('property_bedrooms', $filters['bedrooms']);
        }

        if (! empty($filters['property_ownership_type'])) {
            $query->where('property_ownership_type', $filters['property_ownership_type']);
        }
    }
}
