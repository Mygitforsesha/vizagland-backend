<?php

namespace App\Modules\Property\Services;

use App\Modules\Property\Models\Property;
use App\Modules\Property\Repositories\AdminPropertyRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminPropertyService
{
    public function __construct(
        private readonly AdminPropertyRepository $adminPropertyRepository,
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
}
