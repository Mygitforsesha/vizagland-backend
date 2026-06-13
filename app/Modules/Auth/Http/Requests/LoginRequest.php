<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'user_email' => ['required_without:user_phone', 'nullable', 'string', 'email'],
            'user_phone' => ['required_without:user_email', 'nullable', 'string', 'regex:/^[6-9]\d{9}$/'],
            'user_password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_email.required_without' => 'Email or phone number is required.',
            'user_phone.required_without' => 'Email or phone number is required.',
            'user_email.email' => 'Please provide a valid email address.',
            'user_phone.regex' => 'Please provide a valid 10-digit mobile number.',
            'user_password.required' => 'Password is required.',
        ];
    }
}
