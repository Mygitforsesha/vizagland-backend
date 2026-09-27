<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Requests\Concerns\MapsUserLocationAttributes;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLocationRequest extends FormRequest
{
    use MapsUserLocationAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocationFieldAliases();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->locationFieldRules();
    }
}
