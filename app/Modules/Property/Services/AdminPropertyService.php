<?php

namespace App\Modules\Property\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyOwner;
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
     * @param  list<array{property_owner_name: ?string, property_owner_phone: ?string, property_owner_email: ?string}>|null  $owners
     */
    public function update(int $propertyId, array $attributes, ?array $owners = null): Property
    {
        return DB::transaction(function () use ($propertyId, $attributes, $owners) {
            $property = $this->adminPropertyRepository->findByIdWithDetails($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($property->property_record_type !== PropertyRecordType::VizaglandCopy) {
                throw new RuntimeException('Only VizagLand Copy properties can be updated from admin.');
            }

            $legacyOwnerAttributes = array_intersect_key($attributes, array_flip([
                'property_owner_name',
                'property_owner_phone',
                'property_owner_email',
            ]));

            if ($owners !== null) {
                unset(
                    $attributes['property_owner_name'],
                    $attributes['property_owner_phone'],
                    $attributes['property_owner_email'],
                );
            }

            if ($attributes === [] && $owners === null) {
                return $property;
            }

            $updatedProperty = $attributes === []
                ? $property
                : $this->propertyRepository->update($property, $attributes);

            if ($owners !== null) {
                $this->replaceOwners($updatedProperty, $owners);
            } elseif ($legacyOwnerAttributes !== []) {
                $this->applyLegacyOwner($updatedProperty->fresh() ?? $updatedProperty, $legacyOwnerAttributes);
            }
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

    /**
     * @param  list<array{property_owner_name: ?string, property_owner_phone: ?string, property_owner_email: ?string}>  $owners
     */
    private function replaceOwners(Property $property, array $owners): void
    {
        $property->owners()->delete();

        foreach ($owners as $index => $owner) {
            PropertyOwner::query()->create([
                'property_id' => $property->property_id,
                'property_owner_name' => $owner['property_owner_name'],
                'property_owner_phone' => $owner['property_owner_phone'],
                'property_owner_email' => $owner['property_owner_email'],
                'property_owner_sort_order' => $index,
            ]);
        }

        $first = $owners[0] ?? null;

        $this->propertyRepository->update($property, [
            'property_owner_name' => $first['property_owner_name'] ?? null,
            'property_owner_phone' => $first['property_owner_phone'] ?? null,
            'property_owner_email' => $first['property_owner_email'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $ownerAttributes
     */
    private function applyLegacyOwner(Property $property, array $ownerAttributes): void
    {
        $owner = $property->owners()->orderBy('property_owner_sort_order')->orderBy('property_owner_id')->first();

        if ($owner === null) {
            $name = $ownerAttributes['property_owner_name'] ?? null;
            $phone = $ownerAttributes['property_owner_phone'] ?? null;
            $email = $ownerAttributes['property_owner_email'] ?? null;

            if ($name === null && $phone === null && $email === null) {
                return;
            }

            PropertyOwner::query()->create([
                'property_id' => $property->property_id,
                'property_owner_name' => $name,
                'property_owner_phone' => $phone,
                'property_owner_email' => $email,
                'property_owner_sort_order' => 0,
            ]);

            return;
        }

        $owner->update($ownerAttributes);
    }
}
