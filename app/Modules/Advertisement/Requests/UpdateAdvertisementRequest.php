<?php

namespace App\Modules\Advertisement\Requests;

use App\Modules\Advertisement\Enums\AdvertisementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequest extends FormRequest
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
            'advertisement_title' => ['sometimes', 'required', 'string', 'max:255'],
            'advertisement_description' => ['nullable', 'string', 'max:5000'],
            'advertisement_type' => ['sometimes', 'required', Rule::enum(AdvertisementType::class)],
            'advertisement_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'advertisement_redirect_url' => ['nullable', 'string', 'max:2048', 'url'],
            'advertisement_display_order' => ['nullable', 'integer', 'min:0'],
            'advertisement_start_date' => ['nullable', 'date'],
            'advertisement_end_date' => ['nullable', 'date', 'after_or_equal:advertisement_start_date'],
            'advertisement_is_active' => ['sometimes', 'boolean'],
            'advertisement_village_id' => ['nullable', 'integer', Rule::exists('master_locations', 'master_location_id')],
            'village_id' => ['nullable', 'integer', Rule::exists('master_locations', 'master_location_id')],
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

        if ($this->has('advertisement_type')) {
            $attributes['advertisement_type'] = $this->input('advertisement_type');
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

        if ($this->has('advertisement_village_id') || $this->has('village_id')) {
            $villageId = $this->input('advertisement_village_id') ?? $this->input('village_id');
            $attributes['advertisement_village_id'] = $villageId ? (int) $villageId : null;
        }

        return $attributes;
    }
}
