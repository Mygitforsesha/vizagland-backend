<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyReview
 */
class PropertyReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_review_id' => $this->property_review_id,
            'property_id' => $this->property_id,
            'reviewed_by' => $this->reviewed_by,
            'review_status' => $this->review_status->value,
            'review_status_label' => $this->review_status->label(),
            'review_comments' => $this->review_comments,
            'review_remarks' => $this->review_comments,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'user_id' => $this->reviewer?->id,
                'user_name' => $this->reviewer?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
