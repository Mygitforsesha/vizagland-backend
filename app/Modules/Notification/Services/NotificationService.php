<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Enums\NotificationType;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Repositories\NotificationRepository;
use App\Modules\Property\Models\Property;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function listForUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateForUser($userId, $perPage);
    }

    public function markAsRead(int $notificationId, int $userId): Notification
    {
        $notification = $this->notificationRepository->findActiveForUser($notificationId, $userId);

        if ($notification === null) {
            throw (new ModelNotFoundException)->setModel(Notification::class, [$notificationId]);
        }

        return $this->notificationRepository->markAsRead($notification);
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->notificationRepository->markAllAsReadForUser($userId);
    }

    public function delete(int $notificationId, int $userId): Notification
    {
        $notification = $this->notificationRepository->findActiveForUser($notificationId, $userId);

        if ($notification === null) {
            throw (new ModelNotFoundException)->setModel(Notification::class, [$notificationId]);
        }

        return $this->notificationRepository->softDelete($notification);
    }

    public function notifyUser(
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
    ): Notification {
        return $this->notificationRepository->create([
            'notification_user_id' => $userId,
            'notification_type' => $type,
            'notification_title' => $title,
            'notification_message' => $message,
            'notification_is_read' => false,
        ]);
    }

    /**
     * @param  list<int>  $userIds
     */
    public function notifyUsers(
        array $userIds,
        NotificationType $type,
        string $title,
        string $message,
    ): void {
        $uniqueUserIds = array_values(array_unique(array_filter($userIds)));

        if ($uniqueUserIds === []) {
            return;
        }

        $this->notificationRepository->createMany(
            collect($uniqueUserIds)->map(
                fn (int $userId): array => [
                    'notification_user_id' => $userId,
                    'notification_type' => $type->value,
                    'notification_title' => $title,
                    'notification_message' => $message,
                ],
            )->all(),
        );
    }

    public function notifyAdmins(NotificationType $type, string $title, string $message): void
    {
        $adminUserIds = $this->notificationRepository->adminUsers()
            ->pluck('user_id')
            ->all();

        $this->notifyUsers($adminUserIds, $type, $title, $message);
    }

    public function notifyPropertyCreated(Property $property): void
    {
        if ($property->property_created_by === null) {
            return;
        }

        $referenceId = $property->property_reference_id ?? (string) $property->property_id;

        $this->notifyUser(
            userId: $property->property_created_by,
            type: NotificationType::PropertyCreated,
            title: 'Property Created',
            message: "Property {$referenceId} has been created successfully.",
        );
    }

    public function notifyPropertySubmitted(Property $property): void
    {
        $referenceId = $property->property_reference_id ?? (string) $property->property_id;

        $this->notifyAdmins(
            type: NotificationType::PropertySubmitted,
            title: 'Property Submitted for Review',
            message: "Property {$referenceId} has been submitted and is pending review.",
        );

        if ($property->property_created_by !== null) {
            $this->notifyUser(
                userId: $property->property_created_by,
                type: NotificationType::PropertySubmitted,
                title: 'Property Submitted',
                message: "Your property {$referenceId} has been submitted for review.",
            );
        }
    }

    public function notifyPropertyApproved(Property $property): void
    {
        if ($property->property_created_by === null) {
            return;
        }

        $referenceId = $property->property_reference_id ?? (string) $property->property_id;

        $this->notifyUser(
            userId: $property->property_created_by,
            type: NotificationType::PropertyApproved,
            title: 'Property Approved',
            message: "Your property {$referenceId} has been approved.",
        );
    }

    public function notifyPropertyRejected(Property $property, ?string $reason = null): void
    {
        if ($property->property_created_by === null) {
            return;
        }

        $referenceId = $property->property_reference_id ?? (string) $property->property_id;
        $message = "Your property {$referenceId} has been rejected.";

        if ($reason !== null && $reason !== '') {
            $message .= " Reason: {$reason}";
        }

        $this->notifyUser(
            userId: $property->property_created_by,
            type: NotificationType::PropertyRejected,
            title: 'Property Rejected',
            message: $message,
        );
    }

    public function notifyPropertyResolved(Property $property): void
    {
        $referenceId = $property->property_reference_id ?? (string) $property->property_id;
        $message = "Property {$referenceId} marked as resolved.";

        $this->notifyAdmins(
            type: NotificationType::PropertyResolved,
            title: 'Property Resolved',
            message: $message,
        );

        if ($property->property_created_by !== null) {
            $this->notifyUser(
                userId: $property->property_created_by,
                type: NotificationType::PropertyResolved,
                title: 'Property Resolved',
                message: $message,
            );
        }
    }

    public function notifyUserRegistered(User $user): void
    {
        $this->notifyAdmins(
            type: NotificationType::UserRegistered,
            title: 'New User Registered',
            message: "{$user->user_full_name} ({$user->user_phone}) has registered as {$user->user_role->label()}.",
        );
    }
}
