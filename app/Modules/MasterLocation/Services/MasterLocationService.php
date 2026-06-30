<?php

namespace App\Modules\MasterLocation\Services;

use App\Modules\MasterLocation\Exceptions\MasterLocationDataUnavailableException;
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
}
