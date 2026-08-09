<?php

namespace App\Modules\MasterLocation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListMasterVillagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('q');

        if ((! is_string($query) || trim($query) === '') && is_string($this->input('search'))) {
            $query = $this->input('search');
        }

        if ((! is_string($query) || trim($query) === '') && is_string($this->input('village'))) {
            $query = $this->input('village');
        }

        if (is_string($query)) {
            $this->merge([
                'q' => trim($query),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function searchQuery(): ?string
    {
        $query = $this->input('q');

        if (! is_string($query) || trim($query) === '') {
            return null;
        }

        return trim($query);
    }
}
