<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Property */
class PropertyManagementActionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id' => $this->property_id,
            'property_status' => $this->property_status->value,
        ];
    }
}
