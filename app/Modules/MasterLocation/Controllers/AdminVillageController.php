<?php

namespace App\Modules\MasterLocation\Controllers;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Modules\MasterLocation\Exceptions\MasterLocationDataUnavailableException;
use App\Modules\MasterLocation\Requests\CreateAdminVillageRequest;
use App\Modules\MasterLocation\Requests\ListAdminVillagesRequest;
use App\Modules\MasterLocation\Resources\AdminVillageResource;
use App\Modules\MasterLocation\Services\MasterLocationService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminVillageController extends Controller
{
    public function __construct(
        private readonly MasterLocationService $masterLocationService,
    ) {}

    public function index(ListAdminVillagesRequest $request): JsonResponse
    {
        try {
            $villages = $this->masterLocationService->listAdmin(
                filters: $request->filters(),
                perPage: $request->perPage(),
                sortBy: $request->sortBy(),
                sortDirection: $request->sortDirection(),
            );
        } catch (MasterLocationDataUnavailableException) {
            return $this->errorResponse(
                message: 'Village data is temporarily unavailable. Please try again shortly.',
                statusCode: HttpStatus::SERVICE_UNAVAILABLE,
            );
        }

        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => 'Villages retrieved successfully.',
            'data' => AdminVillageResource::collection($villages->items()),
            'pagination' => [
                'current_page' => $villages->currentPage(),
                'per_page' => $villages->perPage(),
                'total' => $villages->total(),
                'last_page' => $villages->lastPage(),
                'from' => $villages->firstItem(),
                'to' => $villages->lastItem(),
            ],
        ]);
    }

    public function store(CreateAdminVillageRequest $request): JsonResponse
    {
        try {
            $village = $this->masterLocationService->create(
                $request->villageAttributes(),
            );

            return $this->successResponse(
                data: new AdminVillageResource($village),
                message: 'Village created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (MasterLocationDataUnavailableException) {
            return $this->errorResponse(
                message: 'Village data is temporarily unavailable. Please try again shortly.',
                statusCode: HttpStatus::SERVICE_UNAVAILABLE,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create village. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
