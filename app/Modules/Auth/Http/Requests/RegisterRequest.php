<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Http\Requests\Concerns\ValidatesRegistrationTypes;
use App\Modules\User\Enums\UserGender;
use App\Modules\User\Requests\Concerns\MapsUserLocationAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use MapsUserLocationAttributes;
    use ValidatesRegistrationTypes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRegistrationFieldAliases();
        $this->normalizeLocationFieldAliases();

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
    public function rules(): array
    {
        return array_merge($this->registrationTypeRules(), $this->locationFieldRules(), [
            'user_full_name' => ['required', 'string', 'max:255'],
            'user_dob' => ['nullable', 'date', 'before:today'],
            'user_gender' => ['nullable', 'string', Rule::in(UserGender::values())],
            'user_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,user_phone'],
            'user_email' => ['nullable', 'email', 'max:255', 'unique:users,user_email'],
            'user_authority' => ['nullable', 'string', 'max:255'],
            'user_password' => ['required', Password::min(6)],
            'user_password_confirmation' => ['nullable', 'string', 'same:user_password'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        return [
            'user_full_name' => $this->input('user_full_name'),
            'user_phone' => $this->input('user_phone'),
            'user_email' => $this->input('user_email'),
            'user_password' => $this->input('user_password'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileAttributes(): array
    {
        $attributes = array_filter([
            'user_dob' => $this->input('user_dob'),
            'user_gender' => $this->input('user_gender'),
            'user_authority' => $this->input('user_authority'),
        ], fn ($value) => $value !== null && $value !== '');

        return array_merge($attributes, $this->locationProfileAttributes());
    }

    /**
     * @return list<array{user_registration_type_category: string, user_registration_type_value: string}>
     */
    public function normalizedRegistrationTypes(): array
    {
        return $this->buildRegistrationTypesFromInput();
    }

    private function normalizeRegistrationFieldAliases(): void
    {
        $aliases = [];

        if (! $this->filled('user_membership_type') && $this->has('user_membership')) {
            $aliases['user_membership_type'] = $this->input('user_membership');
        }

        if (! $this->filled('user_dob') && $this->has('user_dateOfBirth')) {
            $aliases['user_dob'] = $this->input('user_dateOfBirth');
        }

        if (! $this->filled('user_professions') && $this->has('user_professional')) {
            $value = $this->input('user_professional');

            if ($value !== null && $value !== '') {
                $aliases['user_professions'] = is_array($value) ? $value : [$value];
            }
        }

        if (! $this->filled('user_media_sources') && $this->has('user_media')) {
            $value = $this->input('user_media');

            if ($value !== null && $value !== '') {
                $aliases['user_media_sources'] = is_array($value) ? $value : [$value];
            }
        }

        if (! $this->filled('user_social_media_sources') && $this->has('user_socialMedia')) {
            $value = $this->input('user_socialMedia');

            if ($value !== null && $value !== '') {
                $aliases['user_social_media_sources'] = is_array($value) ? $value : [$value];
            }
        }

        if (! $this->filled('user_other_roles') && $this->has('user_other')) {
            $value = $this->input('user_other');

            if ($value !== null && $value !== '') {
                $aliases['user_other_roles'] = is_array($value) ? $value : [$value];
            }
        }

        if ($aliases !== []) {
            $this->merge($aliases);
        }
    }
}
