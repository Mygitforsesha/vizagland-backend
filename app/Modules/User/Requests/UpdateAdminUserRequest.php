<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Enums\UserRole;
use App\Modules\User\Requests\Concerns\MapsUserProfileAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
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
        $userId = (int) $this->route('user_id');

        return array_merge($this->profileFieldRules(partial: true), [
            'user_full_name' => ['sometimes', 'string', 'max:255'],
            'user_phone' => ['sometimes', 'string', 'regex:/^[6-9]\d{9}$/', Rule::unique('users', 'user_phone')->ignore($userId, 'user_id')],
            'user_email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'user_email')->ignore($userId, 'user_id')],
            'user_role' => ['sometimes', Rule::in([
                UserRole::Admin->value,
                UserRole::Employee->value,
                UserRole::Agent->value,
                UserRole::Member->value,
            ])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        $attributes = [];

        foreach (['user_full_name', 'user_phone', 'user_email', 'user_role'] as $field) {
            if ($this->has($field)) {
                $attributes[$field] = $this->input($field);
            }
        }

        return $attributes;
    }
}
