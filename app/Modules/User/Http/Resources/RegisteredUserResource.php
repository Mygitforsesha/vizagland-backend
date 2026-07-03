<?php

namespace App\Modules\User\Http\Resources;

use App\Modules\User\Enums\RegistrationTypeCategory;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class RegisteredUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'user_full_name' => $this->user_full_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'user_role' => $this->user_role->value,
            'user_role_label' => $this->user_role->label(),
            'user_is_active' => $this->user_is_active,
            'user_last_login_at' => $this->user_last_login_at?->toIso8601String(),
            'user_dob' => $this->profile?->user_dob?->format('Y-m-d'),
            'user_gender' => $this->profile?->user_gender?->value,
            'user_gender_label' => $this->profile?->user_gender?->label(),
            'user_membership_type' => $this->registrationTypeValue(RegistrationTypeCategory::Membership),
            'user_roles' => $this->registrationTypeValues(RegistrationTypeCategory::Role),
            'user_professions' => $this->registrationTypeValues(RegistrationTypeCategory::Professional),
            'user_media_sources' => $this->registrationTypeValues(RegistrationTypeCategory::Media),
            'user_social_media_sources' => $this->registrationTypeValues(RegistrationTypeCategory::SocialMedia),
            'user_other_roles' => $this->registrationTypeValues(RegistrationTypeCategory::Other),
            'user_latitude' => $this->profile?->user_latitude,
            'user_longitude' => $this->profile?->user_longitude,
            'user_google_maps_url' => $this->googleMapsUrl(),
            'user_road' => $this->profile?->user_road,
            'user_colony' => $this->profile?->user_colony,
            'user_suburb' => $this->profile?->user_suburb,
            'user_village' => $this->profile?->user_village,
            'user_nearby_location' => $this->profile?->user_nearby_location,
            'user_custom_nearby_location' => $this->profile?->user_custom_nearby_location,
            'user_mandal' => $this->profile?->user_mandal,
            'user_district' => $this->profile?->user_district,
            'user_panchayati' => $this->profile?->user_panchayati,
            'user_gvmc_zone_ward_number' => $this->profile?->user_gvmc_zone_ward_number,
            'user_vmrda' => $this->profile?->user_vmrda,
            'user_registration_area' => $this->profile?->user_registration_area,
            'user_gvmc_vmrda' => $this->profile?->user_gvmc_vmrda,
            'user_state' => $this->profile?->user_state,
            'user_pincode' => $this->profile?->user_pincode,
            'user_country' => $this->profile?->user_country,
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'registration_types' => UserRegistrationTypeResource::collection($this->whenLoaded('registrationTypes')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<string>
     */
    private function registrationTypeValues(RegistrationTypeCategory $category): array
    {
        if (! $this->relationLoaded('registrationTypes')) {
            return [];
        }

        return $this->registrationTypes
            ->filter(fn ($type) => $type->user_registration_type_category === $category)
            ->map(fn ($type) => $type->user_registration_type_value)
            ->values()
            ->all();
    }

    private function registrationTypeValue(RegistrationTypeCategory $category): ?string
    {
        $values = $this->registrationTypeValues($category);

        return $values[0] ?? null;
    }

    private function googleMapsUrl(): ?string
    {
        $latitude = $this->profile?->user_latitude;
        $longitude = $this->profile?->user_longitude;

        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return null;
        }

        return "https://www.google.com/maps?q={$latitude},{$longitude}";
    }
}
