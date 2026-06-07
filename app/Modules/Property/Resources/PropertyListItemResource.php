<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyListItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_title' => $this->property_title,
            'property_status' => $this->property_status->value,
            'property_source' => $this->property_source?->value,
            'property_price' => $this->property_price,
            'property_city' => $this->property_city,
            'property_created_at' => $this->created_at?->toIso8601String(),
            'images_count' => $this->images_count,
            'documents_count' => $this->documents_count,
        ];
    }
}
