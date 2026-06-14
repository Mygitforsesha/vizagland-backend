<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminPropertyRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage, string $sortBy, string $sortDirection): LengthAwarePaginator
    {
        $query = Property::query()
            ->select([
                'property_id',
                'property_parent_property_id',
                'property_reference_id',
                'property_record_type',
                'property_status',
                'property_owner_name',
                'property_owner_phone',
                'property_price',
                'property_area',
                'property_area_unit',
                'property_village',
                'property_district',
                'property_residential_type',
                'property_commercial_type',
                'property_created_by',
                'property_bedrooms',
                'property_facing',
                'property_furnishing',
                'property_view_count',
                'property_lead_count',
                'property_is_featured',
                'property_review_remarks',
                'property_approved_at',
                'created_at',
                'updated_at',
            ]);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    public function findByIdWithDetails(int $propertyId): ?Property
    {
        return Property::query()
            ->with([
                'createdBy',
                'parentProperty',
                'vizaglandCopy',
                'approvedBy',
                'rejectedBy',
                'archivedBy',
                'restoredBy',
                'images' => fn ($query) => $query->orderBy('property_image_sort_order'),
                'documents' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->where('property_id', $propertyId)
            ->first();
    }

    /**
     * @param  Builder<Property>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('property_reference_id', 'like', '%'.$search.'%')
                    ->orWhere('property_owner_name', 'like', '%'.$search.'%')
                    ->orWhere('property_owner_phone', 'like', '%'.$search.'%')
                    ->orWhere('property_village', 'like', '%'.$search.'%')
                    ->orWhere('property_district', 'like', '%'.$search.'%');

                if (is_numeric($search)) {
                    $builder->orWhere('property_id', (int) $search);
                }
            });
        }

        if (! empty($filters['property_record_type'])) {
            $query->where('property_record_type', $filters['property_record_type']);
        }

        if (! empty($filters['property_status'])) {
            $query->where('property_status', $filters['property_status']);
        }

        if (! empty($filters['property_district'])) {
            $query->where('property_district', $filters['property_district']);
        }

        if (! empty($filters['property_created_by_user_id'])) {
            $query->where('property_created_by', $filters['property_created_by_user_id']);
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applySorting(Builder $query, string $sortBy, string $sortDirection): void
    {
        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'property_id') {
            $query->orderByDesc('property_id');
        }
    }
}
