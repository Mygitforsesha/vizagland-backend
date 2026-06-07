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
            ->orderByDesc('reviewed_at');

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

        if (! empty($filters['review_status'])) {
            $query->where('review_status', $filters['review_status']);
        }

        if (! empty($filters['reviewed_by'])) {
            $query->where('reviewed_by', $filters['reviewed_by']);
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
