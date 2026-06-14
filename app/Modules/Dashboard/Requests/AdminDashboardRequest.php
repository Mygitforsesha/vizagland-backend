<?php

namespace App\Modules\Dashboard\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminDashboardRequest extends FormRequest
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
        return [];
    }
}
