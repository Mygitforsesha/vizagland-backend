<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAdminUsersRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'user_role' => ['nullable', Rule::enum(UserRole::class)],
            'user_is_active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only(['search', 'user_role', 'user_is_active']);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 20);
    }
}
