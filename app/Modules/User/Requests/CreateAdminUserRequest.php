<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Enums\UserRole;
use App\Modules\User\Requests\Concerns\MapsUserProfileAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAdminUserRequest extends FormRequest
{
    use MapsUserProfileAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProfileValidation();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->profileFieldRules(), [
            'user_full_name' => ['required', 'string', 'max:255'],
            'user_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,user_phone'],
            'user_email' => ['nullable', 'email', 'max:255', 'unique:users,user_email'],
            'user_role' => ['required', Rule::in([
                UserRole::Admin->value,
                UserRole::Employee->value,
                UserRole::Agent->value,
                UserRole::Member->value,
            ])],
            'user_password' => ['required', Password::min(6), 'confirmed'],
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
            'user_role' => $this->input('user_role'),
            'user_is_active' => true,
        ];
    }
}
