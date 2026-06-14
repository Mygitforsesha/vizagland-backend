<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Requests\Concerns\MapsUserProfileAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->user_id;

        return array_merge($this->profileFieldRules(partial: true), [
            'user_full_name' => ['sometimes', 'string', 'max:255'],
            'user_email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'user_email')->ignore($userId, 'user_id')],
            'user_phone' => ['prohibited'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        $attributes = [];

        foreach (['user_full_name', 'user_email'] as $field) {
            if ($this->has($field)) {
                $attributes[$field] = $this->input($field);
            }
        }

        return $attributes;
    }
}
