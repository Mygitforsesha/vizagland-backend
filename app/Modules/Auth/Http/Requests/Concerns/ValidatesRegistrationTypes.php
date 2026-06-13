<?php

namespace App\Modules\Auth\Http\Requests\Concerns;

use App\Modules\User\Enums\RegistrationTypeCategory;
use App\Modules\User\Enums\RegistrationTypeValue;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesRegistrationTypes
{
    /**
     * @return array<string, mixed>
     */
    protected function registrationTypeRules(): array
    {
        return [
            'user_membership_type' => [
                'nullable',
                'string',
                Rule::in(RegistrationTypeValue::valuesForCategory(RegistrationTypeCategory::Membership)),
            ],
            'user_roles' => ['nullable', 'array'],
            'user_roles.*' => [
                'required',
                'string',
                Rule::in(RegistrationTypeValue::valuesForCategory(RegistrationTypeCategory::Role)),
            ],
            'user_professions' => ['nullable', 'array'],
            'user_professions.*' => [
                'required',
                'string',
                Rule::in(RegistrationTypeValue::valuesForCategory(RegistrationTypeCategory::Professional)),
            ],
            'user_media_sources' => ['nullable', 'array'],
            'user_media_sources.*' => [
                'required',
                'string',
                Rule::in(RegistrationTypeValue::valuesForCategory(RegistrationTypeCategory::Media)),
            ],
            'user_social_media_sources' => ['nullable', 'array'],
            'user_social_media_sources.*' => [
                'required',
                'string',
                Rule::in(RegistrationTypeValue::valuesForCategory(RegistrationTypeCategory::SocialMedia)),
            ],
            'user_other_roles' => ['nullable', 'array'],
            'user_other_roles.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $seen = [];

            foreach ($this->normalizedRegistrationTypes() as $type) {
                $key = $type['user_registration_type_category'].'|'.$type['user_registration_type_value'];

                if (isset($seen[$key])) {
                    $validator->errors()->add(
                        'user_roles',
                        'Duplicate registration type selection.',
                    );
                }

                $seen[$key] = true;
            }
        });
    }

    /**
     * @return list<array{user_registration_type_category: string, user_registration_type_value: string}>
     */
    protected function buildRegistrationTypesFromInput(): array
    {
        $types = [];

        if ($this->filled('user_membership_type')) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Membership->value,
                'user_registration_type_value' => (string) $this->input('user_membership_type'),
            ];
        }

        foreach ((array) $this->input('user_roles', []) as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Role->value,
                'user_registration_type_value' => (string) $value,
            ];
        }

        foreach ((array) $this->input('user_professions', []) as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Professional->value,
                'user_registration_type_value' => (string) $value,
            ];
        }

        foreach ((array) $this->input('user_media_sources', []) as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Media->value,
                'user_registration_type_value' => (string) $value,
            ];
        }

        foreach ((array) $this->input('user_social_media_sources', []) as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::SocialMedia->value,
                'user_registration_type_value' => (string) $value,
            ];
        }

        foreach ((array) $this->input('user_other_roles', []) as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Other->value,
                'user_registration_type_value' => (string) $value,
            ];
        }

        return $types;
    }
}
