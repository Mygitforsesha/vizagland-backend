<?php

namespace App\Modules\PublicSite\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSupportRequest extends FormRequest
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
            'support_request_name' => ['required', 'string', 'max:255'],
            'support_request_email' => ['required', 'email', 'max:255'],
            'support_request_phone' => ['nullable', 'string', 'max:20'],
            'support_request_message' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function supportAttributes(): array
    {
        return $this->only([
            'support_request_name',
            'support_request_email',
            'support_request_phone',
            'support_request_message',
        ]);
    }
}
