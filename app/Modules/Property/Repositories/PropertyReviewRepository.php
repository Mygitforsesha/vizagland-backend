<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PropertyReviewRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = PropertyReview::query()
            ->with(['property', 'reviewer'])
            ->orderByDesc('property_review_reviewed_at');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<PropertyReview>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        if (! empty($filters['property_review_status'])) {
            $query->where('property_review_status', $filters['property_review_status']);
        }

        if (! empty($filters['property_review_reviewed_by'])) {
            $query->where('property_review_reviewed_by', $filters['property_review_reviewed_by']);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PropertyReview
    {
        return PropertyReview::query()->create($attributes);
    }

    public function updateProperty(Property $property, array $attributes): Property
    {
        $property->update($attributes);

        return $property->fresh();
    }
}
