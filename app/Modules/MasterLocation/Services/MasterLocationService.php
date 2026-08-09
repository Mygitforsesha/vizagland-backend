<?php

namespace App\Modules\MasterLocation\Services;

use App\Modules\MasterLocation\Exceptions\MasterLocationDataUnavailableException;
use App\Modules\MasterLocation\Models\MasterLocation;
use App\Modules\MasterLocation\Repositories\MasterLocationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MasterLocationService
{
    public function __construct(
        private readonly MasterLocationRepository $masterLocationRepository,
        private readonly MasterLocationSetupService $masterLocationSetupService,
    ) {}

    public function search(string $query, int $limit, ?int $page = null): Collection|LengthAwarePaginator
    {
        if (! $this->masterLocationSetupService->isReady()) {
            throw new MasterLocationDataUnavailableException;
        }

        $term = strtolower(trim($query));

        return $this->masterLocationRepository->search(
            term: $term,
            limit: $limit,
            page: $page,
        );
    }

    public function villages(?string $query): Collection|LengthAwarePaginator
    {
        if (! $this->masterLocationSetupService->isReady()) {
            throw new MasterLocationDataUnavailableException;
        }

        if ($query === null || trim($query) === '') {
            return $this->masterLocationRepository->list();
        }

        return $this->masterLocationRepository->search(
            term: strtolower(trim($query)),
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listAdmin(
        array $filters,
        int $perPage,
        string $sortBy,
        string $sortDirection,
    ): LengthAwarePaginator {
        if (! $this->masterLocationSetupService->isReady()) {
            throw new MasterLocationDataUnavailableException;
        }

        return $this->masterLocationRepository->paginateAdmin(
            filters: $filters,
            perPage: $perPage,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): MasterLocation
    {
        if (! $this->masterLocationSetupService->isReady()) {
            throw new MasterLocationDataUnavailableException;
        }

        return $this->masterLocationRepository->create($attributes);
    }
}
