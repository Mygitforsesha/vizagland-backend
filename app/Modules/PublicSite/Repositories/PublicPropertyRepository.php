<?php

namespace App\Modules\PublicSite\Repositories;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PublicPropertyRepository
{
    public function findApprovedById(int $propertyId): ?Property
    {
        return Property::query()
            ->with([
                'images' => fn (Builder $query) => $query->orderBy('property_image_sort_order'),
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
            ->with(['images' => fn (Builder $q) => $q->orderBy('property_image_sort_order')->limit(1)])
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
            ->with(['images' => fn (Builder $q) => $q->orderBy('property_image_sort_order')->limit(1)])
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_status', PropertyStatus::Approved)
            ->where('property_is_featured', true)
            ->orderByDesc('property_published_at')
            ->limit($limit)
            ->get();
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
