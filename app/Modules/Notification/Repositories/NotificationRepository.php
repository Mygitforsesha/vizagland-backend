<?php

namespace App\Modules\Notification\Repositories;

use App\Modules\Notification\Models\Notification;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Notification
    {
        return Notification::query()->create($attributes);
    }

    /**
     * @param  list<array<string, mixed>>  $notifications
     */
    public function createMany(array $notifications): void
    {
        if ($notifications === []) {
            return;
        }

        $now = now();

        Notification::query()->insert(
            collect($notifications)->map(
                fn (array $notification): array => [
                    ...$notification,
                    'notification_is_read' => false,
                    'notification_created_at' => $now,
                ],
            )->all(),
        );
    }

    public function findActiveForUser(int $notificationId, int $userId): ?Notification
    {
        return Notification::query()
            ->active()
            ->where('notification_id', $notificationId)
            ->where('notification_user_id', $userId)
            ->first();
    }

    public function paginateForUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return Notification::query()
            ->active()
            ->where('notification_user_id', $userId)
            ->orderByDesc('notification_created_at')
            ->orderByDesc('notification_id')
            ->paginate($perPage);
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->update([
            'notification_is_read' => true,
            'notification_read_at' => now(),
        ]);

        return $notification->fresh();
    }

    public function markAllAsReadForUser(int $userId): int
    {
        return Notification::query()
            ->active()
            ->where('notification_user_id', $userId)
            ->where('notification_is_read', false)
            ->update([
                'notification_is_read' => true,
                'notification_read_at' => now(),
            ]);
    }

    public function softDelete(Notification $notification): Notification
    {
        $notification->update([
            'notification_deleted_at' => now(),
        ]);

        return $notification->fresh();
    }

    /**
     * @return Collection<int, User>
     */
    public function adminUsers(): Collection
    {
        return User::query()
            ->whereIn('user_role', [UserRole::Admin, UserRole::SuperAdmin])
            ->where('user_is_active', true)
            ->get(['user_id']);
    }
}
