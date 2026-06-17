<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Enums\PropertyListingType;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertySearchSort;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PropertySearchRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, PropertySearchSort $sortBy, int $page, int $perPage): LengthAwarePaginator
    {
        $query = Property::query()
            ->select([
                'property_id',
                'property_reference_id',
                'property_title',
                'property_listing_type',
                'property_price',
                'property_area',
                'property_area_unit',
                'property_village',
                'property_district',
                'property_mandal',
                'property_panchayati',
                'property_residential_type',
                'property_commercial_type',
                'property_development_type',
                'property_layout_type',
                'property_bedrooms',
                'property_bathrooms',
                'property_parking',
                'property_age',
                'property_furnishing',
                'property_facing',
                'property_approval_authority',
                'property_published_at',
                'created_at',
            ])
            ->with(['images' => fn (Builder $builder) => $builder->orderBy('property_image_sort_order')->limit(1)])
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_status', PropertyStatus::Approved)
            ->where('property_is_deleted', false);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sortBy);

        return $query->paginate(perPage: $perPage, page: $page);
    }

    /**
     * @param  Builder<Property>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $this->applyLocationFilter($query, 'property_village', $filters['property_village'] ?? null);
        $this->applyLocationFilter($query, 'property_district', $filters['property_district'] ?? null);
        $this->applyLocationFilter($query, 'property_mandal', $filters['property_mandal'] ?? null);
        $this->applyLocationFilter($query, 'property_panchayati', $filters['property_panchayati'] ?? null);

        $this->applyListingTypeFilter($query, $filters['listing_type'] ?? null);
        $this->applyPropertyGroupFilter($query, $filters['property_group'] ?? []);
        $this->applyPropertyTypeFilter($query, $filters['property_type'] ?? []);

        if (isset($filters['property_price_min'])) {
            $query->where('property_price', '>=', $filters['property_price_min']);
        }

        if (isset($filters['property_price_max'])) {
            $query->where('property_price', '<=', $filters['property_price_max']);
        }

        if (isset($filters['property_area_min'])) {
            $query->where('property_area', '>=', $filters['property_area_min']);
        }

        if (isset($filters['property_area_max'])) {
            $query->where('property_area', '<=', $filters['property_area_max']);
        }

        if (! empty($filters['property_area_unit'])) {
            $normalizedUnit = $this->normalizeAreaUnit((string) $filters['property_area_unit']);
            $query->whereRaw(
                "REPLACE(REPLACE(LOWER(property_area_unit), '.', ''), ' ', '') = ?",
                [$normalizedUnit],
            );
        }

        foreach (['property_bedrooms', 'property_bathrooms', 'property_parking', 'property_total_floors'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (isset($filters['property_balconies']) && Schema::hasColumn('properties', 'property_balconies')) {
            $query->where('property_balconies', $filters['property_balconies']);
        }

        if (! empty($filters['property_age'])) {
            $query->whereRaw('LOWER(property_age) = ?', [strtolower((string) $filters['property_age'])]);
        }

        if (! empty($filters['property_furnishing'])) {
            $query->whereRaw('LOWER(property_furnishing) = ?', [strtolower((string) $filters['property_furnishing'])]);
        }

        if (isset($filters['property_floor_number']) && $filters['property_floor_number'] !== '') {
            $floorNumber = (string) $filters['property_floor_number'];
            $query->where(function (Builder $builder) use ($floorNumber): void {
                $builder->where('property_floor_number', $floorNumber)
                    ->orWhere('property_floor_number', 'like', '%'.$floorNumber.'%');
            });
        }

        $this->applyFacingFilter($query, $filters['property_facing'] ?? []);
        $this->applyApprovalAuthorityFilter($query, $filters['property_approval_authority'] ?? []);
        $this->applyAmenitiesFilter($query, $filters['property_amenities'] ?? []);
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applyLocationFilter(Builder $query, string $column, mixed $value): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $normalized = strtolower(trim($value));

        $query->whereRaw("LOWER({$column}) LIKE ?", ['%'.$normalized.'%']);
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applyListingTypeFilter(Builder $query, mixed $listingType): void
    {
        if (! is_string($listingType) || trim($listingType) === '') {
            return;
        }

        $mapped = match (strtolower(trim($listingType))) {
            'buy' => PropertyListingType::Sale,
            'rent' => PropertyListingType::Rent,
            'lease' => PropertyListingType::Lease,
            'sale' => PropertyListingType::Sale,
            default => PropertyListingType::tryFrom(strtolower(trim($listingType))),
        };

        if ($mapped !== null) {
            $query->where('property_listing_type', $mapped);
        }
    }

    /**
     * @param  Builder<Property>  $query
     * @param  list<mixed>  $groups
     */
    private function applyPropertyGroupFilter(Builder $query, array $groups): void
    {
        if ($groups === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($groups): void {
            foreach ($groups as $group) {
                if (! is_string($group) || trim($group) === '') {
                    continue;
                }

                $normalizedGroup = strtolower(trim($group));

                $builder->orWhere(function (Builder $groupQuery) use ($normalizedGroup): void {
                    match ($normalizedGroup) {
                        'residential' => $groupQuery->whereNotNull('property_residential_type')
                            ->where('property_residential_type', '!=', ''),
                        'commercial' => $groupQuery->whereNotNull('property_commercial_type')
                            ->where('property_commercial_type', '!=', ''),
                        'agricultural' => $groupQuery->where(function (Builder $agriculturalQuery): void {
                            $agriculturalQuery->whereRaw('LOWER(property_layout_type) LIKE ?', ['%farm%'])
                                ->orWhereRaw('LOWER(property_development_type) LIKE ?', ['%farm%'])
                                ->orWhereRaw('LOWER(property_residential_type) LIKE ?', ['%agricultural%'])
                                ->orWhereRaw('LOWER(property_layout_type) LIKE ?', ['%agricultural%']);
                        }),
                        default => $groupQuery->whereRaw('1 = 0'),
                    };
                });
            }
        });
    }

    /**
     * @param  Builder<Property>  $query
     * @param  list<mixed>  $types
     */
    private function applyPropertyTypeFilter(Builder $query, array $types): void
    {
        if ($types === []) {
            return;
        }

        $normalizedTypes = array_values(array_filter(array_map(
            static fn (mixed $type): ?string => is_string($type) && trim($type) !== ''
                ? strtolower(trim($type))
                : null,
            $types,
        )));

        if ($normalizedTypes === []) {
            return;
        }

        $typeColumns = [
            'property_residential_type',
            'property_commercial_type',
            'property_development_type',
            'property_layout_type',
            'property_construction_type',
            'property_construction_status',
        ];

        $query->where(function (Builder $builder) use ($normalizedTypes, $typeColumns): void {
            foreach ($typeColumns as $column) {
                $builder->orWhere(function (Builder $columnQuery) use ($column, $normalizedTypes): void {
                    foreach ($normalizedTypes as $type) {
                        $columnQuery->orWhereRaw("LOWER({$column}) = ?", [$type]);
                    }
                });
            }
        });
    }

    /**
     * @param  Builder<Property>  $query
     * @param  list<mixed>  $facings
     */
    private function applyFacingFilter(Builder $query, array $facings): void
    {
        if ($facings === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($facings): void {
            foreach ($facings as $facing) {
                if (! is_string($facing) || trim($facing) === '') {
                    continue;
                }

                $builder->orWhereRaw('LOWER(property_facing) LIKE ?', ['%'.strtolower(trim($facing)).'%']);
            }
        });
    }

    /**
     * @param  Builder<Property>  $query
     * @param  list<mixed>  $authorities
     */
    private function applyApprovalAuthorityFilter(Builder $query, array $authorities): void
    {
        if ($authorities === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($authorities): void {
            foreach ($authorities as $authority) {
                if (! is_string($authority) || trim($authority) === '') {
                    continue;
                }

                $builder->orWhereRaw('LOWER(property_approval_authority) = ?', [strtolower(trim($authority))]);
            }
        });
    }

    /**
     * @param  Builder<Property>  $query
     * @param  list<mixed>  $amenities
     */
    private function applyAmenitiesFilter(Builder $query, array $amenities): void
    {
        if ($amenities === [] || ! Schema::hasColumn('properties', 'property_amenities')) {
            return;
        }

        foreach ($amenities as $amenity) {
            if (! is_string($amenity) || trim($amenity) === '') {
                continue;
            }

            $query->whereJsonContains('property_amenities', trim($amenity));
        }
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applySorting(Builder $query, PropertySearchSort $sortBy): void
    {
        match ($sortBy) {
            PropertySearchSort::PriceAsc => $query->orderBy('property_price')->orderByDesc('property_id'),
            PropertySearchSort::PriceDesc => $query->orderByDesc('property_price')->orderByDesc('property_id'),
            PropertySearchSort::AreaAsc => $query->orderBy('property_area')->orderByDesc('property_id'),
            PropertySearchSort::Newest => $query
                ->orderByDesc('property_published_at')
                ->orderByDesc('created_at')
                ->orderByDesc('property_id'),
        };
    }

    private function normalizeAreaUnit(string $unit): string
    {
        return str_replace(['.', ' '], '', strtolower(trim($unit)));
    }
}
