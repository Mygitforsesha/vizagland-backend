<?php

namespace App\Modules\MasterLocation\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\MasterLocation\Exceptions\MasterLocationDataUnavailableException;
use App\Modules\MasterLocation\Requests\ListMasterVillagesRequest;
use App\Modules\MasterLocation\Requests\SearchMasterLocationsRequest;
use App\Modules\MasterLocation\Resources\MasterLocationResource;
use App\Modules\MasterLocation\Resources\MasterLocationSearchResource;
use App\Modules\MasterLocation\Services\MasterLocationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class MasterLocationController extends Controller
{
    public function __construct(
        private readonly MasterLocationService $masterLocationService,
    ) {}

    public function search(SearchMasterLocationsRequest $request): JsonResponse
    {
        try {
            $results = $this->masterLocationService->search(
                query: $request->searchQuery(),
                limit: $request->limit(),
                page: $request->page(),
            );
        } catch (MasterLocationDataUnavailableException $exception) {
            return $this->errorResponse(
                message: 'Location search is temporarily unavailable. Please try again shortly.',
                statusCode: HttpStatus::SERVICE_UNAVAILABLE,
            );
        }

        return $this->formatLocationResponse($results);
    }

    public function villages(ListMasterVillagesRequest $request): JsonResponse
    {
        try {
            $results = $this->masterLocationService->villages(
                query: $request->searchQuery(),
            );
        } catch (MasterLocationDataUnavailableException $exception) {
            return $this->errorResponse(
                message: 'Location search is temporarily unavailable. Please try again shortly.',
                statusCode: HttpStatus::SERVICE_UNAVAILABLE,
            );
        }

        return $this->formatLocationResponse($results);
    }

    private function formatLocationResponse(LengthAwarePaginator|\Illuminate\Support\Collection $results): JsonResponse
    {
        if ($results instanceof LengthAwarePaginator) {
            return $this->successResponse(
                data: new MasterLocationSearchResource($results),
                message: 'Master locations retrieved successfully.',
            );
        }

        return $this->successResponse(
            data: MasterLocationResource::collection($results),
            message: 'Master locations retrieved successfully.',
        );
    }
}
