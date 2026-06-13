<?php

namespace App\Modules\User\Http\Resources;

use App\Modules\User\Enums\RegistrationTypeValue;
use App\Modules\User\Models\UserRegistrationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserRegistrationType
 */
class UserRegistrationTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $value = $this->user_registration_type_value;
        $enumValue = RegistrationTypeValue::tryFrom($value);

        return [
            'user_registration_type_id' => $this->user_registration_type_id,
            'user_registration_type_category' => $this->user_registration_type_category->value,
            'user_registration_type_category_label' => $this->user_registration_type_category->label(),
            'user_registration_type_value' => $value,
            'user_registration_type_value_label' => $enumValue?->label() ?? $value,
        ];
    }
}
