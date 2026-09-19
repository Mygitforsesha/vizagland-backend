<?php

namespace App\Modules\OtherService\Controllers;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Modules\OtherService\Requests\CreateOtherServiceRequest;
use App\Modules\OtherService\Requests\ListAdminOtherServicesRequest;
use App\Modules\OtherService\Requests\UpdateOtherServiceRequest;
use App\Modules\OtherService\Requests\UpdateOtherServiceStatusRequest;
use App\Modules\OtherService\Resources\OtherServiceResource;
use App\Modules\OtherService\Services\OtherServiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminOtherServiceController extends Controller
{
    public function __construct(
        private readonly OtherServiceService $otherServiceService,
    ) {}

    public function index(ListAdminOtherServicesRequest $request): JsonResponse
    {
        $otherServices = $this->otherServiceService->listAdmin(
            filters: $request->filters(),
            perPage: $request->perPage(),
            sortBy: $request->sortBy(),
            sortDirection: $request->sortDirection(),
        );

        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => 'Other services retrieved successfully.',
            'data' => OtherServiceResource::collection($otherServices->items()),
            'pagination' => [
                'current_page' => $otherServices->currentPage(),
                'per_page' => $otherServices->perPage(),
                'total' => $otherServices->total(),
                'last_page' => $otherServices->lastPage(),
                'from' => $otherServices->firstItem(),
                'to' => $otherServices->lastItem(),
            ],
        ]);
    }

    public function show(int $other_service_id): JsonResponse
    {
        try {
            $otherService = $this->otherServiceService->show($other_service_id);

            return $this->successResponse(
                data: new OtherServiceResource($otherService),
                message: 'Other service retrieved successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Other service not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function store(CreateOtherServiceRequest $request): JsonResponse
    {
        try {
            $otherService = $this->otherServiceService->create(
                attributes: $request->otherServiceAttributes(),
            );

            return $this->successResponse(
                data: new OtherServiceResource($otherService),
                message: 'Other service created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create other service. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(UpdateOtherServiceRequest $request, int $other_service_id): JsonResponse
    {
        try {
            $otherService = $this->otherServiceService->update(
                otherServiceId: $other_service_id,
                attributes: $request->otherServiceAttributes(),
            );

            return $this->successResponse(
                data: new OtherServiceResource($otherService),
                message: 'Other service updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Other service not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update other service. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function updateStatus(UpdateOtherServiceStatusRequest $request, int $other_service_id): JsonResponse
    {
        try {
            $otherService = $this->otherServiceService->updateStatus(
                otherServiceId: $other_service_id,
                isActive: (bool) $request->validated('other_service_is_active'),
            );

            return $this->successResponse(
                data: new OtherServiceResource($otherService),
                message: 'Other service status updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Other service not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update other service status. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function destroy(int $other_service_id): JsonResponse
    {
        try {
            $otherService = $this->otherServiceService->delete($other_service_id);

            return $this->successResponse(
                data: new OtherServiceResource($otherService),
                message: 'Other service deactivated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Other service not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to deactivate other service. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
