<?php

namespace App\Modules\Property\Services;

use App\Modules\Property\Enums\PropertySearchSort;
use App\Modules\Property\Repositories\PropertySearchHistoryRepository;
use App\Modules\Property\Repositories\PropertySearchRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Throwable;

class PropertySearchService
{
    public function __construct(
        private readonly PropertySearchRepository $propertySearchRepository,
        private readonly PropertySearchHistoryRepository $propertySearchHistoryRepository,
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

    /**
     * @param  array<string, mixed>  $filters
     */
    public function recordHistory(
        ?string $keyword,
        array $filters,
        int $resultsCount,
        ?int $userId = null,
        ?string $ipAddress = null,
    ): void {
        try {
            $this->propertySearchHistoryRepository->create([
                'property_search_history_user_id' => $userId,
                'property_search_history_keyword' => $keyword !== null && trim($keyword) !== ''
                    ? trim($keyword)
                    : null,
                'property_search_history_filters' => $filters,
                'property_search_history_results_count' => $resultsCount,
                'property_search_history_ip_address' => $ipAddress,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function listHistory(int $perPage, int $page): LengthAwarePaginator
    {
        return $this->propertySearchHistoryRepository->paginate($perPage, $page);
    }
}
