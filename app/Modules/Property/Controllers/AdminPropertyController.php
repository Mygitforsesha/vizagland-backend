<?php

namespace App\Modules\Property\Controllers;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\ListAdminPropertiesRequest;
use App\Modules\Property\Requests\UpdateAdminPropertyRequest;
use App\Modules\Property\Resources\AdminPropertyListItemResource;
use App\Modules\Property\Resources\PropertyDetailsResource;
use App\Modules\Property\Services\AdminPropertyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class AdminPropertyController extends Controller
{
    public function __construct(
        private readonly AdminPropertyService $adminPropertyService,
    ) {}

    public function index(ListAdminPropertiesRequest $request): JsonResponse
    {
        $properties = $this->adminPropertyService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
            sortBy: $request->sortBy(),
            sortDirection: $request->sortDirection(),
        );

        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => 'Properties retrieved successfully',
            'data' => AdminPropertyListItemResource::collection($properties->items()),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
                'last_page' => $properties->lastPage(),
                'from' => $properties->firstItem(),
                'to' => $properties->lastItem(),
            ],
        ]);
    }

    public function show(int $property_id): JsonResponse
    {
        try {
            $property = $this->adminPropertyService->show($property_id);

            return $this->successResponse(
                data: new PropertyDetailsResource($property),
                message: 'Property retrieved successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function update(UpdateAdminPropertyRequest $request, int $property_id): JsonResponse
    {
        try {
            $property = $this->adminPropertyService->update(
                propertyId: $property_id,
                attributes: $request->updateAttributes(),
            );

            return $this->successResponse(
                data: new PropertyDetailsResource($property),
                message: 'Property updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: HttpStatus::UNPROCESSABLE_ENTITY,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update property. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
