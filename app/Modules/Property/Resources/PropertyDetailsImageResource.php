<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyImage;
use App\Modules\Property\Services\PropertyMediaStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyImage
 */
class PropertyDetailsImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_image_id' => $this->property_image_id,
            'property_image_original_name' => $this->property_image_original_name,
            'property_image_url' => app(PropertyMediaStorage::class)->url($this->property_image_path),
            'property_image_size' => $this->property_image_size,
            'property_image_mime_type' => $this->property_image_mime_type,
        ];
    }
}
