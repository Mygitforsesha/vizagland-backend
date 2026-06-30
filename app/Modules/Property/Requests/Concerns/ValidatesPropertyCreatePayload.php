<?php

namespace App\Modules\Property\Requests\Concerns;

use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

trait ValidatesPropertyCreatePayload
{
    /**
     * @return list<string>
     */
    protected function prohibitedPropertySystemFields(): array
    {
        return [
            'property_id',
            'property_reference_id',
            'property_status',
            'property_verified',
            'property_submitted_at',
            'property_created_at',
            'property_updated_at',
            'property_record_type',
            'property_parent_property_id',
            'property_view_count',
            'property_lead_count',
            'property_approved_at',
            'property_approved_by_user_id',
            'property_metadata',
            'property_metadata.property_is_featured',
            'property_metadata.property_view_count',
            'property_metadata.property_lead_count',
            'property_metadata.property_is_deleted',
            'property_metadata.property_assigned_user_id',
            'property_metadata.property_review_remarks',
            'property_metadata.property_rejected_reason',
            'property_metadata.property_approved_at',
            'property_metadata.property_approved_by_user_id',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function propertyCreateRules(): array
    {
        $rules = [];

        foreach ($this->prohibitedPropertySystemFields() as $field) {
            $rules[$field] = ['prohibited'];
        }

        $rules = array_merge($rules, [
            'property_approval' => ['nullable', 'array'],
            'property_approval.property_approval_authority' => ['nullable', 'string', 'max:255'],

            'property_location' => ['nullable', 'array'],
            'property_location.property_village' => ['nullable', 'string', 'max:255'],
            'property_location.property_nearby_location' => ['nullable', 'string', 'max:255'],
            'property_location.property_custom_nearby_location' => ['nullable', 'string', 'max:255'],
            'property_location.property_district' => ['nullable', 'string', 'max:255'],
            'property_location.property_mandal' => ['nullable', 'string', 'max:255'],
            'property_location.property_panchayati' => ['nullable', 'string', 'max:255'],
            'property_location.property_gvmc' => ['nullable', 'string', 'max:255'],
            'property_location.property_vmrda' => ['nullable', 'string', 'max:255'],
            'property_location.property_registration_area' => ['nullable', 'string', 'max:255'],
            'property_location.property_authority' => ['nullable', 'string', 'max:255'],

            'property_group_and_types' => ['nullable', 'array'],
            'property_group_and_types.property_residential_type' => ['nullable', 'string', 'max:255'],
            'property_group_and_types.property_commercial_type' => ['nullable', 'string', 'max:255'],
            'property_group_and_types.property_development_type' => ['nullable', 'string', 'max:255'],
            'property_group_and_types.property_layout_type' => ['nullable', 'string', 'max:255'],
            'property_group_and_types.property_construction_status' => ['nullable', 'string', 'max:255'],
            'property_group_and_types.property_construction_type' => ['nullable', 'string', 'max:255'],

            'property_details' => ['nullable', 'array'],
            'property_details.property_price' => ['nullable', 'numeric', 'min:0'],
            'property_details.property_price_range' => ['nullable', 'string', 'max:255'],
            'property_details.property_area' => ['nullable', 'numeric', 'min:0'],
            'property_details.property_area_unit' => ['nullable', 'string', 'max:50'],
            'property_details.property_price_per_sqft' => ['nullable', 'string', 'max:255'],
            'property_details.property_age' => ['nullable', 'string', 'max:255'],
            'property_details.property_facing' => ['nullable', 'string', 'max:255'],
            'property_details.property_total_floors' => ['nullable', 'integer', 'min:0', 'max:200'],
            'property_details.property_floor_number' => ['nullable', 'string', 'max:50'],
            'property_details.property_furnishing' => ['nullable', 'string', 'max:255'],
            'property_details.property_under' => ['nullable', 'string', 'max:255'],
            'property_details.property_lp_no' => ['nullable', 'string', 'max:100'],
            'property_details.property_plot_no' => ['nullable', 'string', 'max:100'],
            'property_details.property_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'property_details.property_bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_details.property_flat_door_no' => ['nullable', 'string', 'max:100'],

            'property_owner' => ['nullable', 'array'],
            'property_owner.property_owner_name' => ['nullable', 'string', 'max:255'],
            'property_owner.property_owner_phone' => ['nullable', 'string', 'max:20'],
            'property_owner.property_owner_email' => ['nullable', 'string', 'max:255', Rule::when(
                fn (): bool => filled(data_get($this->input('property_owner'), 'property_owner_email')),
                ['email'],
            )],

            'property_other_services' => ['nullable', 'array'],
            'property_other_services.property_service_name' => ['nullable', 'string', 'max:255'],
            'property_other_services.property_youtube_video_link' => ['nullable', 'string', 'max:2048'],
            'property_other_services.property_location_link' => ['nullable', 'string', 'max:2048'],

            'property_images' => ['nullable', 'array', 'max:30'],
            'property_images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'property_documents' => ['nullable', 'array', 'max:30'],
            'property_documents.*' => ['file', 'mimes:pdf,doc,docx,zip,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        return app(PropertyFieldConfigurationService::class)
            ->applyFieldConfigurationToValidationRules($rules);
    }

    /**
     * @return array<string, mixed>
     */
    public function propertyAttributes(): array
    {
        $attributes = [];

        foreach ($this->propertyCreateFieldMap() as $group => $fields) {
            foreach ($fields as $field) {
                $value = data_get($this->input($group), $field);

                if ($value !== null && $value !== '') {
                    $attributes[$field] = $value;
                }
            }
        }

        $serviceName = data_get($this->input('property_other_services'), 'property_service_name');

        if ($serviceName !== null && $serviceName !== '') {
            $attributes['property_other_service_name'] = $serviceName;
        }

        $youtubeLink = data_get($this->input('property_other_services'), 'property_youtube_video_link');

        if ($youtubeLink !== null && $youtubeLink !== '') {
            $attributes['property_youtube_video_link'] = $youtubeLink;
        }

        $locationLink = data_get($this->input('property_other_services'), 'property_location_link');

        if ($locationLink !== null && $locationLink !== '') {
            $attributes['property_location_link'] = $locationLink;
        }

        return app(PropertyFieldConfigurationService::class)
            ->filterInactiveFieldAttributes($attributes);
    }

    /**
     * @return array<string, list<string>>
     */
    protected function propertyCreateFieldMap(): array
    {
        return [
            'property_approval' => [
                'property_approval_authority',
            ],
            'property_location' => [
                'property_village',
                'property_nearby_location',
                'property_custom_nearby_location',
                'property_district',
                'property_mandal',
                'property_panchayati',
                'property_gvmc',
                'property_vmrda',
                'property_registration_area',
                'property_authority',
            ],
            'property_group_and_types' => [
                'property_residential_type',
                'property_commercial_type',
                'property_development_type',
                'property_layout_type',
                'property_construction_status',
                'property_construction_type',
            ],
            'property_details' => [
                'property_price',
                'property_price_range',
                'property_area',
                'property_area_unit',
                'property_price_per_sqft',
                'property_age',
                'property_facing',
                'property_total_floors',
                'property_floor_number',
                'property_furnishing',
                'property_under',
                'property_lp_no',
                'property_plot_no',
                'property_year',
                'property_bedrooms',
                'property_flat_door_no',
            ],
            'property_owner' => [
                'property_owner_name',
                'property_owner_phone',
                'property_owner_email',
            ],
        ];
    }

    /**
     * @return list<UploadedFile>
     */
    public function propertyImages(): array
    {
        return $this->normalizeUploadedFiles($this->file('property_images'));
    }

    /**
     * @return list<UploadedFile>
     */
    public function propertyDocuments(): array
    {
        return $this->normalizeUploadedFiles($this->file('property_documents'));
    }

    /**
     * @return list<UploadedFile>
     */
    protected function normalizeUploadedFiles(mixed $files): array
    {
        if ($files === null) {
            return [];
        }

        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn (mixed $file): bool => $file instanceof UploadedFile,
        ));
    }

    protected function preparePropertyUploadsForValidation(): void
    {
        foreach (['property_images', 'property_documents'] as $field) {
            $files = $this->file($field);

            if ($files instanceof UploadedFile) {
                $this->files->set($field, [$files]);
            }
        }
    }
}
