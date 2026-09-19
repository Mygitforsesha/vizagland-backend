<?php

namespace App\Modules\OtherService\Resources;

use App\Modules\OtherService\Models\OtherService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OtherService */
class OtherServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'other_service_id' => $this->other_service_id,
            'other_service_name' => $this->other_service_name,
            'other_service_slug' => $this->other_service_slug,
            'other_service_description' => $this->other_service_description,
            'other_service_icon' => $this->other_service_icon,
            'other_service_sort_order' => $this->other_service_sort_order,
            'other_service_is_active' => $this->other_service_is_active,
            'other_service_created_at' => $this->other_service_created_at?->toIso8601String(),
            'other_service_updated_at' => $this->other_service_updated_at?->toIso8601String(),
        ];
    }
}
