<?php

namespace App\Modules\Advertisement\Requests;

use App\Modules\Advertisement\Enums\AdvertisementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAdminAdvertisementsRequest extends FormRequest
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
            'advertisement_type' => ['nullable', Rule::enum(AdvertisementType::class)],
            'advertisement_is_active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only([
            'search',
            'advertisement_type',
            'advertisement_is_active',
        ]);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 20);
    }
}
