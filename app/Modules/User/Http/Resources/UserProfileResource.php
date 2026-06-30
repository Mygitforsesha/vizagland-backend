<?php

namespace App\Modules\User\Http\Resources;

use App\Modules\User\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserProfile
 */
class UserProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_profile_id' => $this->user_profile_id,
            'user_dob' => $this->user_dob?->format('Y-m-d'),
            'user_gender' => $this->user_gender?->value,
            'user_gender_label' => $this->user_gender?->label(),
            'user_latitude' => $this->user_latitude,
            'user_longitude' => $this->user_longitude,
            'user_road' => $this->user_road,
            'user_colony' => $this->user_colony,
            'user_suburb' => $this->user_suburb,
            'user_village' => $this->user_village,
            'user_nearby_location' => $this->user_nearby_location,
            'user_custom_nearby_location' => $this->user_custom_nearby_location,
            'user_district' => $this->user_district,
            'user_mandal' => $this->user_mandal,
            'user_panchayati' => $this->user_panchayati,
            'user_gvmc_zone_ward_number' => $this->user_gvmc_zone_ward_number,
            'user_vmrda' => $this->user_vmrda,
            'user_registration_area' => $this->user_registration_area,
            'user_gvmc_vmrda' => $this->user_gvmc_vmrda,
            'user_state' => $this->user_state,
            'user_pincode' => $this->user_pincode,
            'user_country' => $this->user_country,
            'user_authority' => $this->user_authority,
        ];
    }
}
