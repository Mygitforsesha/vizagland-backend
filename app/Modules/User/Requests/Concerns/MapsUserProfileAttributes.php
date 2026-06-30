<?php

namespace App\Modules\User\Requests\Concerns;

use App\Modules\User\Enums\UserGender;
use Illuminate\Validation\Rule;

trait MapsUserProfileAttributes
{
    use MapsUserLocationAttributes;

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

        return array_merge([
            'user_dob' => [$prefix, 'date', 'before:today'],
            'user_gender' => [$prefix, 'string', Rule::in(UserGender::values())],
            'user_authority' => [$prefix, 'string', 'max:255'],
        ], $this->locationFieldRules($prefix));
    }

    /**
     * @return array<string, mixed>
     */
    public function profileAttributes(): array
    {
        $fields = [
            'user_dob',
            'user_gender',
            'user_authority',
            ...$this->locationFieldNames(),
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
