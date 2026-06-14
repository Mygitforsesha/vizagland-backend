<?php

namespace App\Modules\PropertyFieldConfiguration\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\PropertyFieldConfiguration\Requests\CreatePropertyFieldConfigurationRequest;
use App\Modules\PropertyFieldConfiguration\Requests\UpdatePropertyFieldConfigurationRequest;
use App\Modules\PropertyFieldConfiguration\Resources\PropertyFieldConfigurationResource;
use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminPropertyFieldController extends Controller
{
    public function __construct(
        private readonly PropertyFieldConfigurationService $propertyFieldConfigurationService,
    ) {}

    public function index(): JsonResponse
    {
        $configurations = $this->propertyFieldConfigurationService->listAll();

        return $this->successResponse(
            data: PropertyFieldConfigurationResource::collection($configurations),
            message: 'Property fields retrieved successfully.',
        );
    }

    public function store(CreatePropertyFieldConfigurationRequest $request): JsonResponse
    {
        try {
            $configuration = $this->propertyFieldConfigurationService->create(
                $request->configurationAttributes(),
            );

            return $this->successResponse(
                data: new PropertyFieldConfigurationResource($configuration),
                message: 'Property field created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create property field. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(UpdatePropertyFieldConfigurationRequest $request, int $id): JsonResponse
    {
        try {
            $configuration = $this->propertyFieldConfigurationService->update(
                propertyFieldConfigurationId: $id,
                attributes: $request->configurationAttributes(),
            );

            return $this->successResponse(
                data: new PropertyFieldConfigurationResource($configuration),
                message: 'Property field updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property field not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update property field. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $configuration = $this->propertyFieldConfigurationService->deactivate($id);

            return $this->successResponse(
                data: new PropertyFieldConfigurationResource($configuration),
                message: 'Property field disabled successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property field not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }
}
