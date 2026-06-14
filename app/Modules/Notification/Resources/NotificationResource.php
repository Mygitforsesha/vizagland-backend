<?php

namespace App\Modules\Notification\Resources;

use App\Modules\Notification\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Notification */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'notification_id' => $this->notification_id,
            'notification_type' => $this->notification_type->value,
            'notification_type_label' => $this->notification_type->label(),
            'notification_title' => $this->notification_title,
            'notification_message' => $this->notification_message,
            'notification_is_read' => $this->notification_is_read,
            'notification_read_at' => $this->notification_read_at?->toIso8601String(),
            'notification_created_at' => $this->notification_created_at?->toIso8601String(),
        ];
    }
}
