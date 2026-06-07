<?php

namespace App\Modules\Property\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\UploadPropertyDocumentRequest;
use App\Modules\Property\Requests\UploadPropertyImageRequest;
use App\Modules\Property\Resources\PropertyDocumentResource;
use App\Modules\Property\Resources\PropertyImageResource;
use App\Modules\Property\Services\PropertyMediaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class PropertyMediaController extends Controller
{
    public function __construct(
        private readonly PropertyMediaService $propertyMediaService,
    ) {}

    public function uploadImage(UploadPropertyImageRequest $request, int $property_id): JsonResponse
    {
        try {
            $image = $this->propertyMediaService->uploadImage(
                propertyId: $property_id,
                image: $request->file('property_image'),
            );

            return $this->successResponse(
                data: new PropertyImageResource($image),
                message: 'Property image uploaded successfully.',
                statusCode: HttpStatus::CREATED,
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
                message: 'Failed to upload property image. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function deleteImage(int $property_image_id): JsonResponse
    {
        try {
            $this->propertyMediaService->deleteImage($property_image_id);

            return $this->successResponse(
                message: 'Property image deleted successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property image not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to delete property image. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function uploadDocument(UploadPropertyDocumentRequest $request, int $property_id): JsonResponse
    {
        try {
            $document = $this->propertyMediaService->uploadDocument(
                propertyId: $property_id,
                document: $request->file('property_document'),
            );

            return $this->successResponse(
                data: new PropertyDocumentResource($document),
                message: 'Property document uploaded successfully.',
                statusCode: HttpStatus::CREATED,
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
                message: 'Failed to upload property document. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function deleteDocument(int $property_document_id): JsonResponse
    {
        try {
            $this->propertyMediaService->deleteDocument($property_document_id);

            return $this->successResponse(
                message: 'Property document deleted successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property document not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to delete property document. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
