<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangeProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', Password::min(6), 'confirmed'],
        ];
    }

    public function currentPassword(): string
    {
        return (string) $this->input('current_password');
    }

    public function newPassword(): string
    {
        return (string) $this->input('new_password');
    }
}
