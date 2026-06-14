<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Requests\Concerns\ValidatesPropertyCreatePayload;
use Illuminate\Foundation\Http\FormRequest;

class CreatePropertyRequest extends FormRequest
{
    use ValidatesPropertyCreatePayload;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->propertyCreateRules();
    }
}
