<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin PropertyImage
 */
class PropertyImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_image_id' => $this->property_image_id,
            'property_id' => $this->property_id,
            'property_image_path' => $this->property_image_path,
            'property_image_url' => Storage::disk('public')->url($this->property_image_path),
            'property_image_name' => $this->property_image_name,
            'property_image_size' => $this->property_image_size,
            'property_image_sort_order' => $this->property_image_sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
