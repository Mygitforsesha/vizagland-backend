<?php

namespace App\Modules\Property\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Property\Requests\ApproveAdminPropertyRequest;
use App\Modules\Property\Requests\ArchiveAdminPropertyRequest;
use App\Modules\Property\Requests\RejectAdminPropertyRequest;
use App\Modules\Property\Requests\RestoreAdminPropertyRequest;
use App\Modules\Property\Resources\PropertyManagementActionResource;
use App\Modules\Property\Services\AdminPropertyManagementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class AdminPropertyActionController extends Controller
{
    public function __construct(
        private readonly AdminPropertyManagementService $adminPropertyManagementService,
    ) {}

    public function approve(ApproveAdminPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleAction(
            fn () => $this->adminPropertyManagementService->approve(
                propertyId: $property_id,
                admin: $request->user(),
                reviewRemarks: $request->reviewRemarks(),
            ),
            'Property approved successfully.',
        );
    }

    public function reject(RejectAdminPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleAction(
            fn () => $this->adminPropertyManagementService->reject(
                propertyId: $property_id,
                admin: $request->user(),
                rejectedReason: $request->rejectedReason(),
            ),
            'Property rejected successfully.',
        );
    }

    public function archive(ArchiveAdminPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleAction(
            fn () => $this->adminPropertyManagementService->archive(
                propertyId: $property_id,
                admin: $request->user(),
                archivedReason: $request->archivedReason(),
            ),
            'Property archived successfully.',
        );
    }

    public function restore(RestoreAdminPropertyRequest $request, int $property_id): JsonResponse
    {
        return $this->handleAction(
            fn () => $this->adminPropertyManagementService->restore(
                propertyId: $property_id,
                admin: $request->user(),
            ),
            'Property restored successfully.',
        );
    }

    private function handleAction(callable $action, string $message): JsonResponse
    {
        try {
            $property = $action();

            return $this->successResponse(
                data: new PropertyManagementActionResource($property),
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
                message: 'Failed to process property action. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
