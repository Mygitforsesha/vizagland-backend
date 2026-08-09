<?php

namespace Tests\Unit\PropertyFieldConfiguration;

use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldDataType;
use App\Modules\PropertyFieldConfiguration\Models\PropertyFieldConfiguration;
use App\Modules\PropertyFieldConfiguration\Repositories\PropertyFieldConfigurationRepository;
use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use App\Modules\ActivityLog\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PublicPropertyFormConfigServiceTest extends TestCase
{
    public function test_public_form_config_returns_exact_section_order_and_field_metadata(): void
    {
        $repository = $this->createMock(PropertyFieldConfigurationRepository::class);
        $activityLog = $this->createMock(ActivityLogService::class);

        $village = new PropertyFieldConfiguration([
            'property_field_key' => 'property_village',
            'property_field_label' => 'Village',
            'property_field_placeholder' => 'Select village',
            'property_field_section' => 'property_location',
            'property_field_data_type' => PropertyFieldDataType::Select,
            'property_field_is_active' => true,
            'property_field_is_required' => true,
            'property_field_is_readonly' => false,
            'property_field_is_searchable' => true,
            'property_field_is_multiple' => false,
            'property_field_options' => null,
            'property_field_options_api' => '/api/master/locations/search',
            'property_field_validation' => null,
            'property_field_default_value' => null,
            'property_field_depends_on' => null,
            'property_field_public_section' => 'property_location',
            'property_field_public_order' => 10,
            'property_field_display_order' => 10,
        ]);
        $village->property_field_configuration_id = 1;

        $ownerEmailShouldNotAppear = new PropertyFieldConfiguration([
            'property_field_key' => 'property_owner_email',
            'property_field_label' => 'Owner Email',
            'property_field_section' => 'property_owner',
            'property_field_data_type' => PropertyFieldDataType::Email,
            'property_field_is_active' => true,
            'property_field_is_required' => false,
            'property_field_public_section' => null,
            'property_field_public_order' => null,
            'property_field_display_order' => 30,
        ]);
        $ownerEmailShouldNotAppear->property_field_configuration_id = 2;

        $repository->method('activePublicFormFields')->willReturn(new Collection([$village]));

        $service = new PropertyFieldConfigurationService($repository, $activityLog);
        $payload = $service->publicFormConfig();

        $this->assertSame([
            'property_location',
            'property_category',
            'property_details',
            'owner_details',
            'other_services',
            'property_contact_numbers',
            'property_images',
            'property_documents',
        ], array_column($payload['sections'], 'key'));

        $location = $payload['sections'][0];
        $this->assertCount(1, $location['fields']);
        $this->assertSame([
            'id',
            'key',
            'label',
            'placeholder',
            'section',
            'order',
            'type',
            'required',
            'active',
            'readonly',
            'searchable',
            'multiple',
            'options',
            'options_api',
            'validation',
            'default_value',
            'depends_on',
        ], array_keys($location['fields'][0]));
        $this->assertSame('property_village', $location['fields'][0]['key']);
        $this->assertSame('/api/master/locations/search', $location['fields'][0]['options_api']);

        $allKeys = [];
        foreach ($payload['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $allKeys[] = $field['key'];
            }
        }
        $this->assertNotContains('property_owner_email', $allKeys);
    }
}
