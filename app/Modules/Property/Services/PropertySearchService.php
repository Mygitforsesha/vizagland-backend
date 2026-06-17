<?php

namespace App\Modules\Property\Services;

use App\Modules\Property\Enums\PropertySearchSort;
use App\Modules\Property\Repositories\PropertySearchRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertySearchService
{
    public function __construct(
        private readonly PropertySearchRepository $propertySearchRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, PropertySearchSort $sortBy, int $page, int $limit): LengthAwarePaginator
    {
        return $this->propertySearchRepository->search(
            filters: $filters,
            sortBy: $sortBy,
            page: $page,
            perPage: $limit,
        );
    }
}
