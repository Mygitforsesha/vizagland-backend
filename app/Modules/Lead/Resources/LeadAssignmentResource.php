<?php

namespace App\Modules\Lead\Resources;

use App\Modules\Lead\Models\LeadAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LeadAssignment
 */
class LeadAssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lead_assignment_id' => $this->lead_assignment_id,
            'lead_id' => $this->lead_id,
            'lead_assigned_to' => $this->lead_assigned_to,
            'lead_assigned_by' => $this->lead_assigned_by,
            'lead_assignment_remarks' => $this->lead_assignment_remarks,
            'assigned_to' => $this->whenLoaded('assignedTo', fn () => [
                'user_id' => $this->assignedTo?->user_id,
                'user_name' => $this->assignedTo?->user_full_name,
            ]),
            'assigned_by' => $this->whenLoaded('assignedBy', fn () => [
                'user_id' => $this->assignedBy?->user_id,
                'user_name' => $this->assignedBy?->user_full_name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
