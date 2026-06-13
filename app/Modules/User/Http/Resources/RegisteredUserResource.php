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
            'user_membership_type' => $this->registrationTypeValue(RegistrationTypeCategory::Membership),
            'user_roles' => $this->registrationTypeValues(RegistrationTypeCategory::Role),
            'user_professions' => $this->registrationTypeValues(RegistrationTypeCategory::Professional),
            'user_media_sources' => $this->registrationTypeValues(RegistrationTypeCategory::Media),
            'user_social_media_sources' => $this->registrationTypeValues(RegistrationTypeCategory::SocialMedia),
            'user_other_roles' => $this->registrationTypeValues(RegistrationTypeCategory::Other),
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
}
