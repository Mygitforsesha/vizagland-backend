<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Requests\Concerns\ValidatesPropertyCreatePayload;
use Illuminate\Foundation\Http\FormRequest;

class CreatePublicPropertyRequest extends FormRequest
{
    use ValidatesPropertyCreatePayload;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->preparePropertyPayloadForValidation();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->propertyCreateRules();
    }

    /**
     * Auth credentials for creating a public_user account (public post only).
     *
     * @return array{username_or_mobile: string, password: string, email: ?string}|null
     */
    public function propertyAuthCredentials(): ?array
    {
        $mobile = data_get($this->input('property_auth'), 'username_or_mobile');
        $password = data_get($this->input('property_auth'), 'password');

        if (! is_string($mobile) || trim($mobile) === '' || ! is_string($password) || $password === '') {
            return null;
        }

        $email = data_get($this->input('property_auth'), 'email');

        return [
            'username_or_mobile' => trim($mobile),
            'password' => $password,
            'email' => is_string($email) && trim($email) !== '' ? trim($email) : null,
        ];
    }
}
