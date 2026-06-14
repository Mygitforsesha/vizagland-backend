<?php

namespace App\Modules\ActivityLog\Resources;

use App\Modules\ActivityLog\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ActivityLog */
class ActivityLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'activity_log_id' => $this->activity_log_id,
            'activity_log_user_id' => $this->activity_log_user_id,
            'activity_log_user_name' => $this->activity_log_user_name,
            'activity_log_user_role' => $this->activity_log_user_role,
            'activity_log_type' => $this->activity_log_type->value,
            'activity_log_type_label' => $this->activity_log_type->label(),
            'activity_log_action' => $this->activity_log_action,
            'activity_log_action_label' => $this->actionLabel(),
            'activity_log_description' => $this->activity_log_description,
            'activity_log_entity_type' => $this->activity_log_entity_type,
            'activity_log_entity_id' => $this->activity_log_entity_id,
            'activity_log_metadata' => $this->activity_log_metadata,
            'activity_log_ip_address' => $this->activity_log_ip_address,
            'activity_log_created_at' => $this->activity_log_created_at?->toIso8601String(),
        ];
    }
}
