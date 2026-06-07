<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Requests\Concerns\ValidatesPropertyAttributes;
use Illuminate\Foundation\Http\FormRequest;

class CreatePropertyRequest extends FormRequest
{
    use ValidatesPropertyAttributes;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->propertyAttributeRules();
    }
}
