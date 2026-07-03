<?php

namespace App\Modules\MasterLocation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchMasterLocationsRequest extends FormRequest
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
            'q' => ['required', 'string', 'min:1', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function searchQuery(): string
    {
        return (string) $this->input('q');
    }

    public function limit(): int
    {
        return (int) ($this->input('limit', 20));
    }

    public function page(): ?int
    {
        if (! $this->filled('page')) {
            return null;
        }

        return (int) $this->input('page');
    }
}
