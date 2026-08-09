<?php

namespace App\Modules\Property\Requests;

use App\Modules\Property\Enums\PropertySearchSort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPropertiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $searchFilters = $this->input('search_filters');

        if (is_array($searchFilters)) {
            $this->merge($searchFilters);
        }

        $this->merge([
            'page' => $this->input('page', 1),
            'limit' => $this->input('limit', 6),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search_keyword' => ['nullable', 'string', 'max:255'],
            'search_filters' => ['nullable', 'array'],
            'property_village' => ['nullable', 'string', 'max:255'],
            'property_district' => ['nullable', 'string', 'max:255'],
            'property_mandal' => ['nullable', 'string', 'max:255'],
            'property_panchayati' => ['nullable', 'string', 'max:255'],
            'listing_type' => ['nullable', 'string', 'max:50'],
            'property_group' => ['nullable', 'array'],
            'property_group.*' => ['required', 'string', 'max:255'],
            'property_type' => ['nullable', 'array'],
            'property_type.*' => ['required', 'string', 'max:255'],
            'property_price_min' => ['nullable', 'numeric', 'min:0'],
            'property_price_max' => ['nullable', 'numeric', 'min:0'],
            'property_area_min' => ['nullable', 'numeric', 'min:0'],
            'property_area_max' => ['nullable', 'numeric', 'min:0'],
            'property_area_unit' => ['nullable', 'string', 'max:50'],
            'property_bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_balconies' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_parking' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_age' => ['nullable', 'string', 'max:255'],
            'property_furnishing' => ['nullable', 'string', 'max:255'],
            'property_total_floors' => ['nullable', 'integer', 'min:0', 'max:200'],
            'property_floor_number' => ['nullable'],
            'property_facing' => ['nullable', 'array'],
            'property_facing.*' => ['required', 'string', 'max:255'],
            'property_approval_authority' => ['nullable', 'array'],
            'property_approval_authority.*' => ['required', 'string', 'max:255'],
            'property_amenities' => ['nullable', 'array'],
            'property_amenities.*' => ['required', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::enum(PropertySearchSort::class)],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only([
            'property_village',
            'property_district',
            'property_mandal',
            'property_panchayati',
            'listing_type',
            'property_group',
            'property_type',
            'property_price_min',
            'property_price_max',
            'property_area_min',
            'property_area_max',
            'property_area_unit',
            'property_bedrooms',
            'property_bathrooms',
            'property_balconies',
            'property_parking',
            'property_age',
            'property_furnishing',
            'property_total_floors',
            'property_floor_number',
            'property_facing',
            'property_approval_authority',
            'property_amenities',
        ]);
    }

    public function sortBy(): PropertySearchSort
    {
        $sort = $this->input('sort_by');

        return PropertySearchSort::tryFrom((string) $sort) ?? PropertySearchSort::Newest;
    }

    public function page(): int
    {
        return (int) $this->input('page', 1);
    }

    public function limit(): int
    {
        return (int) $this->input('limit', 6);
    }

    public function searchKeyword(): ?string
    {
        $keyword = $this->input('search_keyword');

        if (! is_string($keyword) || trim($keyword) === '') {
            return null;
        }

        return trim($keyword);
    }

    /**
     * @return array<string, mixed>
     */
    public function historyFilters(): array
    {
        $filters = $this->filters();
        $filters['sort_by'] = $this->sortBy()->value;
        $filters['page'] = $this->page();
        $filters['limit'] = $this->limit();

        return array_filter(
            $filters,
            static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== [],
        );
    }
}
