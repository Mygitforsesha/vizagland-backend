<?php

namespace App\Modules\FollowUp\Resources;

use App\Modules\FollowUp\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FollowUp
 */
class FollowUpResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'follow_up_id' => $this->follow_up_id,
            'follow_up_type' => $this->follow_up_type->value,
            'follow_up_type_label' => $this->follow_up_type->label(),
            'follow_up_notes' => $this->follow_up_notes,
            'follow_up_scheduled_at' => $this->follow_up_scheduled_at?->toIso8601String(),
            'follow_up_completed_at' => $this->follow_up_completed_at?->toIso8601String(),
            'follow_up_status' => $this->follow_up_status->value,
            'follow_up_status_label' => $this->follow_up_status->label(),
            'follow_up_property_id' => $this->follow_up_property_id,
            'follow_up_lead_id' => $this->follow_up_lead_id,
            'follow_up_created_by' => $this->follow_up_created_by,
            'follow_up_assigned_to' => $this->follow_up_assigned_to,
            'property' => $this->whenLoaded('property', fn () => [
                'property_id' => $this->property?->property_id,
                'property_title' => $this->property?->property_title,
            ]),
            'lead' => $this->whenLoaded('lead', fn () => [
                'lead_id' => $this->lead?->lead_id,
                'lead_name' => $this->lead?->lead_name,
            ]),
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'user_id' => $this->createdBy?->id,
                'user_name' => $this->createdBy?->name,
            ]),
            'assigned_to' => $this->whenLoaded('assignedTo', fn () => [
                'user_id' => $this->assignedTo?->id,
                'user_name' => $this->assignedTo?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
