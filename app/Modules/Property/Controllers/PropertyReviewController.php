<?php

namespace App\Modules\Property\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\ListPropertyReviewsRequest;
use App\Modules\Property\Requests\ReviewPropertyRequest;
use App\Modules\Property\Resources\PropertyReviewListResource;
use App\Modules\Property\Resources\PropertyReviewResource;
use App\Modules\Property\Services\PropertyReviewService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class PropertyReviewController extends Controller
{
    public function __construct(
        private readonly PropertyReviewService $propertyReviewService,
    ) {}

    public function index(ListPropertyReviewsRequest $request): JsonResponse
    {
        $reviews = $this->propertyReviewService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new PropertyReviewListResource($reviews),
        );
    }

    public function approve(ReviewPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleReviewAction(
            fn () => $this->propertyReviewService->approve(
                propertyId: $property_id,
                reviewer: $request->user(),
                remarks: $request->remarks(),
            ),
            'Property approved successfully.',
        );
    }

    public function reject(ReviewPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleReviewAction(
            fn () => $this->propertyReviewService->reject(
                propertyId: $property_id,
                reviewer: $request->user(),
                remarks: $request->remarks(),
            ),
            'Property rejected successfully.',
        );
    }

    public function requestChanges(ReviewPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleReviewAction(
            fn () => $this->propertyReviewService->requestChanges(
                propertyId: $property_id,
                reviewer: $request->user(),
                remarks: $request->remarks(),
            ),
            'Change request recorded successfully.',
        );
    }

    private function handleReviewAction(callable $action, string $message): JsonResponse
    {
        try {
            $review = $action();

            return $this->successResponse(
                data: new PropertyReviewResource($review->load('reviewer')),
                message: $message,
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
                message: 'Failed to process property review. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
