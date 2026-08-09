<?php

namespace App\Modules\Property\Controllers;

use App\Enums\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\ListPropertySearchHistoryRequest;
use App\Modules\Property\Requests\SearchPropertiesRequest;
use App\Modules\Property\Resources\PropertySearchHistoryResource;
use App\Modules\Property\Resources\PropertySearchListResource;
use App\Modules\Property\Services\PropertySearchService;
use Illuminate\Http\JsonResponse;

class PropertySearchController extends Controller
{
    public function __construct(
        private readonly PropertySearchService $propertySearchService,
    ) {}

    public function search(SearchPropertiesRequest $request): JsonResponse
    {
        $properties = $this->propertySearchService->search(
            filters: $request->filters(),
            sortBy: $request->sortBy(),
            page: $request->page(),
            limit: $request->limit(),
        );

        $this->propertySearchService->recordHistory(
            keyword: $request->searchKeyword(),
            filters: $request->historyFilters(),
            resultsCount: $properties->total(),
            userId: $request->user()?->user_id,
            ipAddress: $request->ip(),
        );

        return $this->successResponse(
            data: new PropertySearchListResource($properties),
            message: 'Properties retrieved successfully.',
        );
    }

    public function history(ListPropertySearchHistoryRequest $request): JsonResponse
    {
        $history = $this->propertySearchService->listHistory(
            perPage: $request->perPage(),
            page: $request->page(),
        );

        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => 'Property search history retrieved successfully.',
            'data' => PropertySearchHistoryResource::collection($history->items()),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
                'last_page' => $history->lastPage(),
                'from' => $history->firstItem(),
                'to' => $history->lastItem(),
            ],
        ]);
    }
}
