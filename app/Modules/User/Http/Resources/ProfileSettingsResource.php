<?php

namespace App\Modules\User\Http\Resources;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class ProfileSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->relationLoaded('profile') ? $this->profile : null;

        return [
            'user_id' => $this->user_id,
            'user_full_name' => $this->user_full_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'user_role' => $this->user_role->value,
            'user_role_label' => $this->user_role->label(),
            'profile' => $profile === null
                ? $this->emptyProfile()
                : new ProfileSettingsProfileResource($profile),
        ];
    }

    /**
     * @return array<string, null>
     */
    private function emptyProfile(): array
    {
        return [
            'user_dob' => null,
            'user_gender' => null,
            'user_latitude' => null,
            'user_longitude' => null,
            'user_road' => null,
            'user_colony' => null,
            'user_suburb' => null,
            'user_village' => null,
            'user_nearby_location' => null,
            'user_custom_nearby_location' => null,
            'user_district' => null,
            'user_mandal' => null,
            'user_panchayati' => null,
            'user_gvmc_zone_ward_number' => null,
            'user_vmrda' => null,
            'user_registration_area' => null,
            'user_gvmc_vmrda' => null,
            'user_state' => null,
            'user_pincode' => null,
            'user_country' => null,
            'user_authority' => null,
        ];
    }
}
