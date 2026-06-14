<?php

namespace App\Modules\User\Requests\Concerns;

use App\Modules\User\Enums\UserGender;
use Illuminate\Validation\Rule;

trait MapsUserProfileAttributes
{
    protected function prepareProfileValidation(): void
    {
        $gender = $this->input('user_gender');

        if (is_string($gender)) {
            $gender = strtolower(trim($gender));
            $this->merge([
                'user_gender' => $gender === '' ? null : $gender,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function profileFieldRules(bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes' : 'nullable';

        return [
            'user_dob' => [$prefix, 'date', 'before:today'],
            'user_gender' => [$prefix, 'string', Rule::in(UserGender::values())],
            'user_village' => [$prefix, 'string', 'max:255'],
            'user_nearby_location' => [$prefix, 'string', 'max:255'],
            'user_custom_nearby_location' => [$prefix, 'string', 'max:255'],
            'user_district' => [$prefix, 'string', 'max:255'],
            'user_mandal' => [$prefix, 'string', 'max:255'],
            'user_panchayati' => [$prefix, 'string', 'max:255'],
            'user_gvmc_zone_ward_number' => [$prefix, 'string', 'max:255'],
            'user_vmrda' => [$prefix, 'string', 'max:255'],
            'user_registration_area' => [$prefix, 'string', 'max:255'],
            'user_authority' => [$prefix, 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileAttributes(): array
    {
        $fields = [
            'user_dob',
            'user_gender',
            'user_village',
            'user_nearby_location',
            'user_custom_nearby_location',
            'user_district',
            'user_mandal',
            'user_panchayati',
            'user_gvmc_zone_ward_number',
            'user_vmrda',
            'user_registration_area',
            'user_authority',
        ];

        $attributes = [];

        foreach ($fields as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                continue;
            }

            $attributes[$field] = $value;
        }

        return $attributes;
    }
}
