<?php

namespace App\Modules\Advertisement\Requests;

use App\Modules\Advertisement\Enums\AdvertisementCategory;
use App\Modules\MasterLocation\Models\MasterLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = [];

        // Decode advertisement_types / advertisement_type if passed as string or JSON
        $types = $this->input('advertisement_types');
        $typeStr = $this->input('advertisement_type');

        if (is_string($types)) {
            $decoded = json_decode($types, true);
            if (is_array($decoded)) {
                $types = $decoded;
            } else {
                $types = array_filter(array_map('trim', explode(',', $types)));
            }
        }

        if (empty($types) && is_string($typeStr)) {
            $decodedType = json_decode($typeStr, true);
            if (is_array($decodedType)) {
                $types = $decodedType;
            } else {
                $types = array_filter(array_map('trim', explode(',', $typeStr)));
            }
        }

        if (is_array($types)) {
            $input['advertisement_types'] = array_values($types);
            $input['advertisement_type'] = implode(',', $types);
        }

        // Decode property_location
        $location = $this->input('property_location');
        if (is_string($location)) {
            $decodedLocation = json_decode($location, true);
            if (is_array($decodedLocation)) {
                $input['property_location'] = $decodedLocation;
            }
        }

        // Decode property_details
        $details = $this->input('property_details');
        if (is_string($details)) {
            $decodedDetails = json_decode($details, true);
            if (is_array($decodedDetails)) {
                $input['property_details'] = $decodedDetails;
            }
        }

        // Cast boolean string
        if ($this->has('advertisement_is_active')) {
            $activeVal = $this->input('advertisement_is_active');
            if (is_string($activeVal)) {
                $input['advertisement_is_active'] = filter_var($activeVal, FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (! empty($input)) {
            $this->merge($input);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'advertisement_title' => ['sometimes', 'required', 'string', 'max:255'],
            'advertisement_description' => ['nullable', 'string', 'max:5000'],
            'advertisement_types' => ['nullable', 'array'],
            'advertisement_type' => ['nullable'],
            'advertisement_category' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'advertisement_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'advertisement_redirect_url' => ['nullable', 'string', 'max:2048'],
            'advertisement_display_order' => ['nullable', 'integer', 'min:0'],
            'advertisement_start_date' => ['nullable', 'date'],
            'advertisement_end_date' => ['nullable', 'date', 'after_or_equal:advertisement_start_date'],
            'advertisement_is_active' => ['sometimes', 'boolean'],
            'advertisement_village_id' => ['nullable', 'integer', Rule::exists('master_locations', 'master_location_id')],
            'village_id' => ['nullable', 'integer', Rule::exists('master_locations', 'master_location_id')],
            'master_location_id' => ['nullable', 'integer', Rule::exists('master_locations', 'master_location_id')],
            'property_category' => ['nullable', 'string', 'max:255'],
            'property_location' => ['nullable', 'array'],
            'property_details' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function advertisementAttributes(): array
    {
        $attributes = [];

        if ($this->has('advertisement_title')) {
            $attributes['advertisement_title'] = $this->input('advertisement_title');
        }

        if ($this->has('advertisement_description')) {
            $attributes['advertisement_description'] = $this->input('advertisement_description');
        }

        if ($this->has('advertisement_types') || $this->has('advertisement_type')) {
            $types = $this->input('advertisement_types');
            if (! is_array($types) && $this->input('advertisement_type')) {
                $types = array_filter(array_map('trim', explode(',', (string) $this->input('advertisement_type'))));
            }

            if (is_array($types)) {
                $attributes['advertisement_types'] = array_values($types);
                $attributes['advertisement_type'] = implode(',', $types);

                if (in_array('village_wise_ads', $types, true) || in_array('village_wise', $types, true)) {
                    $attributes['advertisement_category'] = 'village_wise';
                } elseif (in_array('latest_ads', $types, true) || in_array('latest', $types, true)) {
                    $attributes['advertisement_category'] = 'latest';
                } elseif (in_array('general_ads', $types, true) || in_array('general', $types, true)) {
                    $attributes['advertisement_category'] = 'general';
                }
            } else {
                $attributes['advertisement_type'] = $this->input('advertisement_type');
            }
        }

        if ($this->has('advertisement_category') || $this->has('category')) {
            $rawCategory = $this->input('advertisement_category') ?? $this->input('category');
            $attributes['advertisement_category'] = AdvertisementCategory::fromValue($rawCategory)->value;
        }

        if ($this->has('advertisement_redirect_url')) {
            $attributes['advertisement_redirect_url'] = $this->input('advertisement_redirect_url');
        }

        if ($this->has('advertisement_display_order')) {
            $attributes['advertisement_display_order'] = (int) $this->input('advertisement_display_order');
        }

        if ($this->has('advertisement_start_date')) {
            $attributes['advertisement_start_date'] = $this->input('advertisement_start_date');
        }

        if ($this->has('advertisement_end_date')) {
            $attributes['advertisement_end_date'] = $this->input('advertisement_end_date');
        }

        if ($this->has('advertisement_is_active')) {
            $attributes['advertisement_is_active'] = $this->boolean('advertisement_is_active');
        }

        if ($this->has('advertisement_village_id') || $this->has('village_id') || $this->has('master_location_id')) {
            $villageId = $this->input('advertisement_village_id')
                ?? $this->input('village_id')
                ?? $this->input('master_location_id');
            $attributes['advertisement_village_id'] = $villageId ? (int) $villageId : null;
        }

        if ($this->has('property_location')) {
            $location = $this->input('property_location');
            $attributes['property_location'] = is_array($location) ? $location : null;

            if (! isset($attributes['advertisement_village_id']) && is_array($location) && ! empty($location['property_village'])) {
                $villageName = trim((string) $location['property_village']);
                $masterLoc = MasterLocation::query()
                    ->whereRaw('LOWER(master_location_village) = ?', [mb_strtolower($villageName)])
                    ->orWhere('master_location_village', 'like', '%'.$villageName.'%')
                    ->first();

                if ($masterLoc !== null) {
                    $attributes['advertisement_village_id'] = $masterLoc->master_location_id;
                }
            }
        }

        if ($this->has('property_category')) {
            $attributes['property_category'] = $this->input('property_category');
        }

        if ($this->has('property_details')) {
            $details = $this->input('property_details');
            $attributes['property_details'] = is_array($details) ? $details : null;
        }

        return $attributes;
    }
}
