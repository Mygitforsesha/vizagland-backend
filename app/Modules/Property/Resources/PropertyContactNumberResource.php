<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyContactNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyContactNumber
 */
class PropertyContactNumberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_contact_number_id' => $this->property_contact_number_id,
            'registration_type' => $this->property_contact_number_registration_type,
            'phone_number' => $this->property_contact_number_phone_number,
        ];
    }
}
