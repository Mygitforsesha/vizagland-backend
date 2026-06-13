<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Http\Requests\Concerns\ValidatesRegistrationTypes;
use App\Modules\User\Enums\UserGender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use ValidatesRegistrationTypes;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->registrationTypeRules(), [
            'user_full_name' => ['required', 'string', 'max:255'],
            'user_dob' => ['nullable', 'date', 'before:today'],
            'user_gender' => ['nullable', Rule::enum(UserGender::class)],
            'user_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,user_phone'],
            'user_email' => ['nullable', 'email', 'max:255', 'unique:users,user_email'],
            'user_village' => ['nullable', 'string', 'max:255'],
            'user_nearby_location' => ['nullable', 'string', 'max:255'],
            'user_custom_nearby_location' => ['nullable', 'string', 'max:255'],
            'user_district' => ['nullable', 'string', 'max:255'],
            'user_mandal' => ['nullable', 'string', 'max:255'],
            'user_panchayati' => ['nullable', 'string', 'max:255'],
            'user_gvmc_zone_ward_number' => ['nullable', 'string', 'max:255'],
            'user_vmrda' => ['nullable', 'string', 'max:255'],
            'user_registration_area' => ['nullable', 'string', 'max:255'],
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
        return [
            'user_dob' => $this->input('user_dob'),
            'user_gender' => $this->input('user_gender'),
            'user_village' => $this->input('user_village'),
            'user_nearby_location' => $this->input('user_nearby_location'),
            'user_custom_nearby_location' => $this->input('user_custom_nearby_location'),
            'user_district' => $this->input('user_district'),
            'user_mandal' => $this->input('user_mandal'),
            'user_panchayati' => $this->input('user_panchayati'),
            'user_gvmc_zone_ward_number' => $this->input('user_gvmc_zone_ward_number'),
            'user_vmrda' => $this->input('user_vmrda'),
            'user_registration_area' => $this->input('user_registration_area'),
            'user_authority' => $this->input('user_authority'),
        ];
    }

    /**
     * @return list<array{user_registration_type_category: string, user_registration_type_value: string}>
     */
    public function normalizedRegistrationTypes(): array
    {
        return $this->buildRegistrationTypesFromInput();
    }
}
