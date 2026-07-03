<?php

namespace App\Modules\Property\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Repositories\AdminPropertyRepository;
use App\Modules\Property\Repositories\PropertyRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminPropertyService
{
    public function __construct(
        private readonly AdminPropertyRepository $adminPropertyRepository,
        private readonly PropertyRepository $propertyRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage, string $sortBy, string $sortDirection): LengthAwarePaginator
    {
        return $this->adminPropertyRepository->paginate($filters, $perPage, $sortBy, $sortDirection);
    }

    public function show(int $propertyId): Property
    {
        $property = $this->adminPropertyRepository->findByIdWithDetails($propertyId);

        if ($property === null) {
            throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
        }

        return $property;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $propertyId, array $attributes): Property
    {
        return DB::transaction(function () use ($propertyId, $attributes) {
            $property = $this->adminPropertyRepository->findByIdWithDetails($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($property->property_record_type !== PropertyRecordType::VizaglandCopy) {
                throw new RuntimeException('Only VizagLand Copy properties can be updated from admin.');
            }

            if ($attributes === []) {
                return $property;
            }

            $updatedProperty = $this->propertyRepository->update($property, $attributes);
            $referenceId = $updatedProperty->property_reference_id ?? (string) $updatedProperty->property_id;

            $this->activityLogService->log(
                type: ActivityLogType::Property,
                action: 'updated',
                description: "Updated VizagLand Copy property {$referenceId}",
                entityType: 'property',
                entityId: $updatedProperty->property_id,
                metadata: ['property_reference_id' => $referenceId],
            );

            $refreshed = $this->adminPropertyRepository->findByIdWithDetails($propertyId);

            if ($refreshed === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            return $refreshed;
        });
    }
}
