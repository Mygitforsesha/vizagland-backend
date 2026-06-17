<?php

namespace App\Modules\Advertisement\Controllers;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Modules\Advertisement\Requests\CreateAdvertisementRequest;
use App\Modules\Advertisement\Requests\ListAdminAdvertisementsRequest;
use App\Modules\Advertisement\Requests\UpdateAdvertisementRequest;
use App\Modules\Advertisement\Resources\AdvertisementResource;
use App\Modules\Advertisement\Services\AdvertisementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminAdvertisementController extends Controller
{
    public function __construct(
        private readonly AdvertisementService $advertisementService,
    ) {}

    public function index(ListAdminAdvertisementsRequest $request): JsonResponse
    {
        $advertisements = $this->advertisementService->listAdmin(
            filters: $request->filters(),
            perPage: $request->perPage(),
        );

        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => 'Advertisements retrieved successfully.',
            'data' => AdvertisementResource::collection($advertisements->items()),
            'pagination' => [
                'current_page' => $advertisements->currentPage(),
                'per_page' => $advertisements->perPage(),
                'total' => $advertisements->total(),
                'last_page' => $advertisements->lastPage(),
                'from' => $advertisements->firstItem(),
                'to' => $advertisements->lastItem(),
            ],
        ]);
    }

    public function show(int $advertisement_id): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService->show($advertisement_id);

            return $this->successResponse(
                data: new AdvertisementResource($advertisement),
                message: 'Advertisement retrieved successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Advertisement not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function store(CreateAdvertisementRequest $request): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService->create(
                attributes: $request->advertisementAttributes(),
                image: $request->file('advertisement_image'),
                user: $request->user(),
            );

            return $this->successResponse(
                data: new AdvertisementResource($advertisement),
                message: 'Advertisement created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create advertisement. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(UpdateAdvertisementRequest $request, int $advertisement_id): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService->update(
                advertisementId: $advertisement_id,
                attributes: $request->advertisementAttributes(),
                image: $request->file('advertisement_image'),
            );

            return $this->successResponse(
                data: new AdvertisementResource($advertisement),
                message: 'Advertisement updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Advertisement not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update advertisement. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function destroy(int $advertisement_id): JsonResponse
    {
        try {
            $this->advertisementService->delete($advertisement_id);

            return $this->successResponse(
                message: 'Advertisement deleted successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Advertisement not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to delete advertisement. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
