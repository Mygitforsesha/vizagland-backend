<?php

namespace App\Modules\MasterLocation\Requests;

use App\Modules\MasterLocation\Models\MasterLocation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class CreateAdminVillageRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    private const STRING_FIELDS = [
        'master_location_village',
        'master_location_nearby_location',
        'master_location_additional_nearby_location',
        'master_location_district',
        'master_location_mandal',
        'master_location_panchayati',
        'master_location_gvmc_zone',
        'master_location_gvmc_ward',
        'master_location_vmrda',
        'master_location_registration_office',
        'master_location_authority',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (self::STRING_FIELDS as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            $normalized[$field] = $trimmed === '' ? null : $trimmed;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'master_location_village' => [
                'required',
                'string',
                'max:255',
                $this->uniqueVillageWithinDistrictAndMandalRule(),
            ],
            'master_location_nearby_location' => ['nullable', 'string', 'max:255'],
            'master_location_additional_nearby_location' => ['nullable', 'string', 'max:255'],
            'master_location_district' => ['nullable', 'string', 'max:255'],
            'master_location_mandal' => ['nullable', 'string', 'max:255'],
            'master_location_panchayati' => ['nullable', 'string', 'max:255'],
            'master_location_gvmc_zone' => ['nullable', 'string', 'max:255'],
            'master_location_gvmc_ward' => ['nullable', 'string', 'max:255'],
            'master_location_vmrda' => ['nullable', 'string', 'max:255'],
            'master_location_registration_office' => ['nullable', 'string', 'max:255'],
            'master_location_authority' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'master_location_village.required' => 'The village name is required.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function villageAttributes(): array
    {
        $attributes = [];

        foreach (self::STRING_FIELDS as $field) {
            $attributes[$field] = $this->input($field);
        }

        return $attributes;
    }

    private function uniqueVillageWithinDistrictAndMandalRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $query = MasterLocation::query()
                ->whereRaw('LOWER(master_location_village) = ?', [mb_strtolower($value)]);

            $district = $this->input('master_location_district');
            $mandal = $this->input('master_location_mandal');

            if (is_string($district) && $district !== '') {
                $query->whereRaw('LOWER(master_location_district) = ?', [mb_strtolower($district)]);
            } else {
                $query->whereNull('master_location_district');
            }

            if (is_string($mandal) && $mandal !== '') {
                $query->whereRaw('LOWER(master_location_mandal) = ?', [mb_strtolower($mandal)]);
            } else {
                $query->whereNull('master_location_mandal');
            }

            if ($query->exists()) {
                $fail('A village with this name already exists for the given district and mandal.');
            }
        };
    }
}
