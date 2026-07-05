<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyContactNumber;
use App\Modules\Property\Models\PropertyDocument;
use App\Modules\Property\Models\PropertyImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PropertyRepository
{
    public function findById(int $propertyId): ?Property
    {
        return Property::query()
            ->where('property_id', $propertyId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Property $property, array $attributes): Property
    {
        $property->update($attributes);

        return $property->fresh();
    }

    public function findByIdWithDetails(int $propertyId): ?Property
    {
        return Property::query()
            ->with([
                'createdBy',
                'parentProperty',
                'vizaglandCopy',
                'images' => fn ($query) => $query->orderBy('property_image_sort_order'),
                'documents' => fn ($query) => $query->orderBy('created_at'),
                'contactNumbers' => fn ($query) => $query->orderBy('property_contact_number_id'),
            ])
            ->where('property_id', $propertyId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage, string $sort): LengthAwarePaginator
    {
        $query = Property::query()
            ->select([
                'property_id',
                'property_reference_id',
                'property_title',
                'property_status',
                'property_source',
                'property_price',
                'property_city',
                'created_at',
            ])
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->withCount(['images', 'documents']);

        $this->applyFilters($query, $filters);

        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<Property>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['property_status'])) {
            $query->where('property_status', $filters['property_status']);
        }

        if (! empty($filters['property_source'])) {
            $query->where('property_source', $filters['property_source']);
        }

        if (! empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (! empty($filters['property_city'])) {
            $query->where('property_city', $filters['property_city']);
        }

        if (! empty($filters['property_created_by_type'])) {
            $query->where('property_created_by_type', $filters['property_created_by_type']);
        }

        if (! empty($filters['property_created_by_id'])) {
            $query->where('property_created_by_id', $filters['property_created_by_id']);
        }

        if (isset($filters['price_min'])) {
            $query->where('property_price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('property_price', '<=', $filters['price_max']);
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Property
    {
        return Property::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createImage(int $propertyId, array $attributes): PropertyImage
    {
        return PropertyImage::query()->create([
            'property_id' => $propertyId,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createDocument(int $propertyId, array $attributes): PropertyDocument
    {
        return PropertyDocument::query()->create([
            'property_id' => $propertyId,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createContactNumber(int $propertyId, array $attributes): PropertyContactNumber
    {
        return PropertyContactNumber::query()->create([
            'property_id' => $propertyId,
            ...$attributes,
        ]);
    }
}
