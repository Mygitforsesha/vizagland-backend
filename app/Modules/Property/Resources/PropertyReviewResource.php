<?php

namespace App\Modules\Property\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'property_review_reviewed_by' => $this->property_review_reviewed_by,
            'property_review_status' => $this->property_review_status->value,
            'property_review_status_label' => $this->property_review_status->label(),
            'property_review_comments' => $this->property_review_comments,
            'property_review_reviewed_at' => $this->property_review_reviewed_at?->toIso8601String(),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'user_id' => $this->reviewer?->user_id,
                'user_name' => $this->reviewer?->user_full_name,
            ]),
        ];
    }
}
