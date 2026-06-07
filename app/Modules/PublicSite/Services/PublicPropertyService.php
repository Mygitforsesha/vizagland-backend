<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Property\Models\Property;
use App\Modules\PublicSite\Repositories\PublicPropertyRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicPropertyService
{
    public function __construct(
        private readonly PublicPropertyRepository $publicPropertyRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->publicPropertyRepository->paginateApproved($filters, $perPage);
    }

    public function show(int $propertyId): Property
    {
        $property = $this->publicPropertyRepository->findApprovedById($propertyId);

        if ($property === null) {
            throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
        }

        return $property;
    }

    /**
     * @return Collection<int, Property>
     */
    public function featured(int $limit = 10): Collection
    {
        return $this->publicPropertyRepository->featured($limit);
    }
}
