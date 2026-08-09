<?php

namespace App\Modules\Property\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\CreatePropertyRequest;
use App\Modules\Property\Requests\CreatePublicPropertyRequest;
use App\Modules\Property\Requests\ListMyPropertiesRequest;
use App\Modules\Property\Requests\ListPropertiesRequest;
use App\Modules\Property\Requests\SubmitPropertyForReviewRequest;
use App\Modules\Property\Requests\UpdatePropertyRequest;
use App\Modules\Property\Resources\PropertyCreatedResource;
use App\Modules\Property\Resources\PropertyDetailsResource;
use App\Modules\Property\Resources\PropertyListResource;
use App\Modules\Property\Resources\PropertyMyListResource;
use App\Modules\Property\Resources\PropertyResource;
use App\Modules\Property\Services\PropertyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService,
    ) {}

    public function index(ListPropertiesRequest $request): JsonResponse
    {
        $properties = $this->propertyService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
            sort: $request->sort(),
        );

        return $this->successResponse(
            data: new PropertyListResource($properties),
        );
    }

    public function myProperties(ListMyPropertiesRequest $request): JsonResponse
    {
        $properties = $this->propertyService->listMyPropertiesByPhone(
            phoneNumber: $request->phoneNumber(),
            perPage: $request->perPage(),
            sort: $request->sort(),
        );

        return $this->successResponse(
            data: new PropertyMyListResource($properties),
        );
    }

    public function show(Request $request, int $property_id): JsonResponse
    {
        try {
            $property = $this->propertyService->show(
                propertyId: $property_id,
                user: $request->user(),
            );

            return $this->successResponse(
                data: new PropertyDetailsResource($property),
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: HttpStatus::FORBIDDEN,
            );
        }
    }

    public function update(UpdatePropertyRequest $request, int $property_id): JsonResponse
    {
        try {
            $property = $this->propertyService->update(
                propertyId: $property_id,
                attributes: $request->updateAttributes(),
            );

            return $this->successResponse(
                data: new PropertyResource($property),
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

    public function store(CreatePropertyRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $user !== null
                ? $this->propertyService->createAuthenticated(
                    data: $request->propertyAttributes(),
                    images: $request->propertyImages(),
                    documents: $request->propertyDocuments(),
                    user: $user,
                    contactNumbers: $request->propertyContactNumbers(),
                )
                : $this->propertyService->createPublic(
                    data: $request->propertyAttributes(),
                    images: $request->propertyImages(),
                    documents: $request->propertyDocuments(),
                    contactNumbers: $request->propertyContactNumbers(),
                );

            return $this->successResponse(
                data: new PropertyCreatedResource($result),
                message: 'Property submitted successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create property. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function submitForReview(SubmitPropertyForReviewRequest $request, int $property_id): JsonResponse
    {
        try {
            $property = $this->propertyService->submitForReview(
                propertyId: $property_id,
                user: $request->user(),
            );

            return $this->successResponse(
                data: new PropertyResource($property),
                message: 'Property submitted for review successfully.',
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
                message: 'Failed to submit property for review. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function storePublic(CreatePublicPropertyRequest $request): JsonResponse
    {
        try {
            $result = $this->propertyService->createPublic(
                data: $request->propertyAttributes(),
                images: $request->propertyImages(),
                documents: $request->propertyDocuments(),
                contactNumbers: $request->propertyContactNumbers(),
                authCredentials: $request->propertyAuthCredentials(),
            );

            return $this->successResponse(
                data: new PropertyCreatedResource($result),
                message: 'Property submitted successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create property. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
