<?php

namespace App\Modules\Auth\Http\Requests\Concerns;

use App\Modules\User\Enums\RegistrationTypeCategory;
use Illuminate\Validation\Validator;

trait ValidatesRegistrationTypes
{
    /**
     * @return array<string, mixed>
     */
    protected function registrationTypeRules(): array
    {
        return [
            'user_membership' => ['nullable', 'string', 'max:255'],
            'user_membership_type' => ['nullable', 'string', 'max:255'],
            'user_roles' => ['nullable', 'array'],
            'user_roles.*' => ['required', 'string', 'max:255'],
            'user_professional' => ['nullable'],
            'user_professions' => ['nullable', 'array'],
            'user_professions.*' => ['required', 'string', 'max:255'],
            'user_media' => ['nullable'],
            'user_media_sources' => ['nullable', 'array'],
            'user_media_sources.*' => ['required', 'string', 'max:255'],
            'user_socialMedia' => ['nullable'],
            'user_social_media_sources' => ['nullable', 'array'],
            'user_social_media_sources.*' => ['required', 'string', 'max:255'],
            'user_other' => ['nullable'],
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

        $membership = $this->input('user_membership_type') ?? $this->input('user_membership');

        if ($this->isPresentRegistrationValue($membership)) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Membership->value,
                'user_registration_type_value' => $this->normalizeRegistrationValue($membership),
            ];
        }

        foreach ($this->registrationValues('user_roles') as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Role->value,
                'user_registration_type_value' => $value,
            ];
        }

        foreach ($this->registrationValues('user_professions', 'user_professional') as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Professional->value,
                'user_registration_type_value' => $value,
            ];
        }

        foreach ($this->registrationValues('user_media_sources', 'user_media') as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Media->value,
                'user_registration_type_value' => $value,
            ];
        }

        foreach ($this->registrationValues('user_social_media_sources', 'user_socialMedia') as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::SocialMedia->value,
                'user_registration_type_value' => $value,
            ];
        }

        foreach ($this->registrationValues('user_other_roles', 'user_other') as $value) {
            $types[] = [
                'user_registration_type_category' => RegistrationTypeCategory::Other->value,
                'user_registration_type_value' => $value,
            ];
        }

        return $types;
    }

    /**
     * @return list<string>
     */
    protected function registrationValues(string $primaryKey, ?string $alternateKey = null): array
    {
        $values = $this->input($primaryKey);

        if (($values === null || $values === '') && $alternateKey !== null && $this->has($alternateKey)) {
            $values = $this->input($alternateKey);
        }

        if ($values === null || $values === '') {
            return [];
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): ?string => $this->isPresentRegistrationValue($value)
                ? $this->normalizeRegistrationValue($value)
                : null,
            $values,
        )));
    }

    protected function isPresentRegistrationValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    protected function normalizeRegistrationValue(mixed $value): string
    {
        return trim((string) $value);
    }
}
