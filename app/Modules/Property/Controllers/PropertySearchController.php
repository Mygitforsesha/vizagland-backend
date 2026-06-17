<?php

namespace App\Modules\Property\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\SearchPropertiesRequest;
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

        return $this->successResponse(
            data: new PropertySearchListResource($properties),
            message: 'Properties retrieved successfully.',
        );
    }
}
