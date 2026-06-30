<?php

namespace App\Modules\User\Http\Resources;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->relationLoaded('profile') ? $this->profile : null;

        return array_merge([
            'user_id' => $this->user_id,
            'user_full_name' => $this->user_full_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'user_role' => $this->user_role->value,
            'user_role_label' => $this->user_role->label(),
            'user_is_active' => $this->user_is_active,
            'user_last_login_at' => $this->user_last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ], $this->locationFields($profile));
    }

    /**
     * @return array<string, mixed>
     */
    private function locationFields(?object $profile): array
    {
        return [
            'user_latitude' => $profile?->user_latitude,
            'user_longitude' => $profile?->user_longitude,
            'user_road' => $profile?->user_road,
            'user_colony' => $profile?->user_colony,
            'user_suburb' => $profile?->user_suburb,
            'user_village' => $profile?->user_village,
            'user_nearby_location' => $profile?->user_nearby_location,
            'user_custom_nearby_location' => $profile?->user_custom_nearby_location,
            'user_mandal' => $profile?->user_mandal,
            'user_district' => $profile?->user_district,
            'user_panchayati' => $profile?->user_panchayati,
            'user_gvmc_zone_ward_number' => $profile?->user_gvmc_zone_ward_number,
            'user_vmrda' => $profile?->user_vmrda,
            'user_registration_area' => $profile?->user_registration_area,
            'user_gvmc_vmrda' => $profile?->user_gvmc_vmrda,
            'user_state' => $profile?->user_state,
            'user_pincode' => $profile?->user_pincode,
            'user_country' => $profile?->user_country,
        ];
    }
}
