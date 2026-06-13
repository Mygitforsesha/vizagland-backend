<?php

namespace App\Modules\Lead\Resources;

use App\Modules\Lead\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lead
 */
class LeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lead_id' => $this->lead_id,
            'lead_name' => $this->lead_name,
            'lead_email' => $this->lead_email,
            'lead_phone' => $this->lead_phone,
            'lead_message' => $this->lead_message,
            'lead_source' => $this->lead_source->value,
            'lead_source_label' => $this->lead_source->label(),
            'lead_status' => $this->lead_status->value,
            'lead_status_label' => $this->lead_status->label(),
            'lead_property_id' => $this->lead_property_id,
            'lead_created_by' => $this->lead_created_by,
            'lead_assigned_to' => $this->lead_assigned_to,
            'property' => $this->whenLoaded('property', fn () => [
                'property_id' => $this->property?->property_id,
                'property_title' => $this->property?->property_title,
            ]),
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'user_id' => $this->createdBy?->user_id,
                'user_name' => $this->createdBy?->user_full_name,
            ]),
            'assigned_to' => $this->whenLoaded('assignedTo', fn () => [
                'user_id' => $this->assignedTo?->user_id,
                'user_name' => $this->assignedTo?->user_full_name,
            ]),
            'assignments' => LeadAssignmentResource::collection($this->whenLoaded('assignments')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
