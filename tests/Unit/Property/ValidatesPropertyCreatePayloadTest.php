<?php

namespace Tests\Unit\Property;

use App\Modules\Property\Requests\CreatePublicPropertyRequest;
use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidatesPropertyCreatePayloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mock = $this->createMock(PropertyFieldConfigurationService::class);
        $mock->method('applyFieldConfigurationToValidationRules')
            ->willReturnCallback(static fn (array $rules): array => $rules);
        $mock->method('filterInactiveFieldAttributes')
            ->willReturnCallback(static fn (array $attributes): array => $attributes);

        $this->app->instance(PropertyFieldConfigurationService::class, $mock);
    }
    public function test_complete_payload_passes_validation(): void
    {
        $payload = [
            'registration_type' => 'Developer',
            'property_approval' => [
                'property_approval_authority' => 'GVMC',
            ],
            'property_location' => [
                'property_village' => 'Madhurawada',
            ],
            'property_details' => [
                'property_project_name' => 'Sunrise Villas',
                'property_year' => 2020,
                'property_document_year' => 2019,
            ],
            'property_auth' => [
                'username_or_mobile' => '9876543210',
                'password' => 'Secret@123',
                'email' => 'poster@example.com',
            ],
            'property_other_services' => [
                'property_youtube_video_links' => [
                    ['url' => 'https://www.youtube.com/watch?v=abc123'],
                    ['url' => 'https://www.youtube.com/watch?v=def456'],
                ],
                'property_location_links' => [
                    ['url' => 'https://maps.google.com/?q=17.7,83.2'],
                ],
            ],
            'property_posting_location' => [
                'user_latitude' => 17.728945,
                'user_longitude' => 83.305678,
                'user_road' => 'Beach Road',
                'user_district' => 'Visakhapatnam',
                'user_country' => 'India',
            ],
            'property_contact_numbers' => [
                ['registration_type' => 'Owner', 'phone_number' => '9876543210'],
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->all()));
    }

    public function test_legacy_singular_other_service_links_still_map(): void
    {
        $payload = [
            'property_other_services' => [
                'property_youtube_video_link' => 'https://www.youtube.com/watch?v=legacy1',
                'property_location_link' => 'https://maps.google.com/?q=17.1,83.1',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $attributes = $request->propertyAttributes();

        $this->assertSame('https://www.youtube.com/watch?v=legacy1', $attributes['property_youtube_video_link']);
        $this->assertSame(
            [['url' => 'https://www.youtube.com/watch?v=legacy1']],
            $attributes['property_youtube_video_links'],
        );
        $this->assertSame('https://maps.google.com/?q=17.1,83.1', $attributes['property_location_link']);
        $this->assertSame(
            [['url' => 'https://maps.google.com/?q=17.1,83.1']],
            $attributes['property_location_links'],
        );
    }

    public function test_repeater_links_and_posting_location_are_mapped(): void
    {
        $payload = [
            'property_other_services' => [
                'property_youtube_video_links' => [
                    ['url' => 'https://www.youtube.com/watch?v=abc123'],
                    ['url' => ''],
                    ['url' => 'https://www.youtube.com/watch?v=def456'],
                ],
                'property_location_links' => [
                    ['url' => 'https://maps.google.com/?q=17.7,83.2'],
                ],
            ],
            'property_posting_location' => [
                'user_latitude' => 17.728945,
                'user_longitude' => 83.305678,
                'user_village' => null,
                'user_road' => '',
                'user_district' => 'Visakhapatnam',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $attributes = $request->propertyAttributes();

        $this->assertSame(
            [
                ['url' => 'https://www.youtube.com/watch?v=abc123'],
                ['url' => 'https://www.youtube.com/watch?v=def456'],
            ],
            $attributes['property_youtube_video_links'],
        );
        $this->assertSame('https://www.youtube.com/watch?v=abc123', $attributes['property_youtube_video_link']);
        $this->assertSame(
            [['url' => 'https://maps.google.com/?q=17.7,83.2']],
            $attributes['property_location_links'],
        );
        $this->assertSame([
            'user_latitude' => 17.728945,
            'user_longitude' => 83.305678,
            'user_district' => 'Visakhapatnam',
        ], $attributes['property_posting_location']);
    }

    public function test_empty_numeric_strings_are_normalized_to_null(): void
    {
        $payload = [
            'property_details' => [
                'property_price' => '',
                'property_year' => '',
                'property_total_floors' => '',
                'property_document_year' => '',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->all()));

        $attributes = $request->propertyAttributes();

        $this->assertSame([], $attributes);
    }

    public function test_property_attributes_maps_registration_type_and_nested_fields(): void
    {
        $payload = [
            'registration_type' => 'Owner',
            'property_location' => [
                'property_village' => 'Test Village',
            ],
            'property_details' => [
                'property_project_name' => 'Project X',
                'property_block_phase' => 'Phase 1',
                'property_document_no' => 'DOC-1',
                'property_registration_office_area' => 'Visakhapatnam',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $attributes = $request->propertyAttributes();

        $this->assertSame('Owner', $attributes['property_registration_type']);
        $this->assertSame('Test Village', $attributes['property_village']);
        $this->assertSame('Project X', $attributes['property_project_name']);
        $this->assertSame('Phase 1', $attributes['property_block_phase']);
        $this->assertSame('DOC-1', $attributes['property_document_no']);
        $this->assertSame('Visakhapatnam', $attributes['property_registration_office_area']);
    }

    public function test_property_contact_numbers_are_mapped(): void
    {
        $payload = [
            'property_contact_numbers' => [
                ['registration_type' => 'Owner', 'phone_number' => '9876543210'],
                ['registration_type' => '', 'phone_number' => ''],
                ['registration_type' => 'Agent', 'phone_number' => '9123456780'],
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $contactNumbers = $request->propertyContactNumbers();

        $this->assertCount(2, $contactNumbers);
        $this->assertSame('Owner', $contactNumbers[0]['property_contact_number_registration_type']);
        $this->assertSame('9876543210', $contactNumbers[0]['property_contact_number_phone_number']);
    }

    public function test_backend_controlled_fields_are_prohibited(): void
    {
        $payload = [
            'property_id' => 99,
            'property_status' => 'approved',
            'property_metadata' => [
                'property_is_featured' => true,
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('property_id'));
        $this->assertTrue($validator->errors()->has('property_status'));
        $this->assertTrue($validator->errors()->has('property_metadata'));
    }

    public function test_nan_and_invalid_bedrooms_are_stripped_from_attributes(): void
    {
        $payload = [
            'property_details' => [
                'property_bedrooms' => 'NaN',
                'property_project_name' => 'Test Project',
                'property_year' => 'undefined',
                'property_total_floors' => 'Infinity',
            ],
            'property_owner' => [
                'property_owner_name' => 'raju',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $validator = Validator::make($request->all(), $request->rules());
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->all()));

        $attributes = $request->propertyAttributes();

        $this->assertSame('Test Project', $attributes['property_project_name']);
        $this->assertSame('raju', $attributes['property_owner_name']);
        $this->assertArrayNotHasKey('property_bedrooms', $attributes);
        $this->assertArrayNotHasKey('property_year', $attributes);
        $this->assertArrayNotHasKey('property_total_floors', $attributes);
    }

    public function test_five_plus_bedrooms_are_coerced_to_integer(): void
    {
        $payload = [
            'property_details' => [
                'property_bedrooms' => '5_plus',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $attributes = $request->propertyAttributes();

        $this->assertSame(5, $attributes['property_bedrooms']);
    }

    public function test_approval_authority_accepted_under_property_details(): void
    {
        $payload = [
            'property_details' => [
                'property_approval_authority' => 'gvmc',
            ],
        ];

        $request = CreatePublicPropertyRequest::create('/api/public/properties', 'POST', $payload);
        $request->setContainer(app());
        $this->invokePrepareForValidation($request);

        $attributes = $request->propertyAttributes();

        $this->assertSame('gvmc', $attributes['property_approval_authority']);
    }

    private function invokePrepareForValidation(CreatePublicPropertyRequest $request): void
    {
        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);
    }
}
