<?php

namespace App\Modules\Notification\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Notification\Requests\ListNotificationsRequest;
use App\Modules\Notification\Resources\NotificationListResource;
use App\Modules\Notification\Resources\NotificationResource;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $notifications = $this->notificationService->listForUser(
            userId: $request->user()->user_id,
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new NotificationListResource($notifications),
            message: 'Notifications retrieved successfully.',
        );
    }

    public function markAsRead(Request $request, int $notification_id): JsonResponse
    {
        try {
            $notification = $this->notificationService->markAsRead(
                notificationId: $notification_id,
                userId: $request->user()->user_id,
            );

            return $this->successResponse(
                data: new NotificationResource($notification),
                message: 'Notification marked as read.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Notification not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updatedCount = $this->notificationService->markAllAsRead(
            userId: $request->user()->user_id,
        );

        return $this->successResponse(
            data: ['updated_count' => $updatedCount],
            message: 'All notifications marked as read.',
        );
    }

    public function destroy(Request $request, int $notification_id): JsonResponse
    {
        try {
            $this->notificationService->delete(
                notificationId: $notification_id,
                userId: $request->user()->user_id,
            );

            return $this->successResponse(
                message: 'Notification deleted successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Notification not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }
}
