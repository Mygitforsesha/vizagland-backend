<?php

namespace Tests\Feature\Property;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyContactNumber;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_property_create_accepts_complete_nested_payload(): void
    {
        Storage::fake('property_media');

        $payload = $this->samplePayload();

        $response = $this->postJson('/api/public/properties', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'original_property_id',
                    'vizagland_copy_property_id',
                    'property_reference_id',
                ],
            ]);

        $originalId = $response->json('data.original_property_id');
        $copyId = $response->json('data.vizagland_copy_property_id');

        $original = Property::query()->findOrFail($originalId);
        $copy = Property::query()->findOrFail($copyId);

        $this->assertSame(PropertyRecordType::Original, $original->property_record_type);
        $this->assertSame(PropertyRecordType::VizaglandCopy, $copy->property_record_type);
        $this->assertSame($original->property_id, $copy->property_parent_property_id);
        $this->assertSame($original->property_reference_id, $copy->property_reference_id);
        $this->assertSame('Developer', $original->property_registration_type);
        $this->assertSame('GVMC', $original->property_approval_authority);
        $this->assertSame('Madhurawada', $original->property_village);
        $this->assertSame('Sunrise Villas', $original->property_project_name);
        $this->assertSame(2020, $original->property_year);
        $this->assertNull($original->property_total_floors);
        $this->assertSame('https://www.youtube.com/watch?v=abc123', $original->property_youtube_video_link);

        $this->assertSame(2, PropertyContactNumber::query()->where('property_id', $originalId)->count());
        $this->assertSame(2, PropertyContactNumber::query()->where('property_id', $copyId)->count());
    }

    public function test_public_property_create_accepts_partial_payload_with_empty_numeric_strings(): void
    {
        Storage::fake('property_media');

        $response = $this->postJson('/api/public/properties', [
            'registration_type' => 'Owner',
            'property_details' => [
                'property_price' => '',
                'property_year' => '',
                'property_total_floors' => '',
                'property_document_year' => '',
            ],
        ]);

        $response->assertCreated();

        $copyId = $response->json('data.vizagland_copy_property_id');
        $copy = Property::query()->findOrFail($copyId);

        $this->assertSame('Owner', $copy->property_registration_type);
        $this->assertNull($copy->property_price);
        $this->assertNull($copy->property_year);
        $this->assertNull($copy->property_total_floors);
        $this->assertNull($copy->property_document_year);
    }

    public function test_public_property_create_accepts_multiple_contact_numbers(): void
    {
        Storage::fake('property_media');

        $response = $this->postJson('/api/public/properties', [
            'property_contact_numbers' => [
                ['registration_type' => 'Owner', 'phone_number' => '9876543210'],
                ['registration_type' => 'Agent', 'phone_number' => '9123456780'],
            ],
        ]);

        $response->assertCreated();

        $originalId = $response->json('data.original_property_id');

        $contacts = PropertyContactNumber::query()
            ->where('property_id', $originalId)
            ->orderBy('property_contact_number_id')
            ->get();

        $this->assertCount(2, $contacts);
        $this->assertSame('Owner', $contacts[0]->property_contact_number_registration_type);
        $this->assertSame('9876543210', $contacts[0]->property_contact_number_phone_number);
    }

    public function test_public_property_create_accepts_no_contact_numbers(): void
    {
        Storage::fake('property_media');

        $response = $this->postJson('/api/public/properties', [
            'property_location' => [
                'property_village' => 'Test Village',
            ],
        ]);

        $response->assertCreated();

        $originalId = $response->json('data.original_property_id');

        $this->assertSame(0, PropertyContactNumber::query()->where('property_id', $originalId)->count());
    }

    public function test_property_auth_is_accepted_but_not_persisted(): void
    {
        Storage::fake('property_media');

        $response = $this->postJson('/api/public/properties', [
            'property_auth' => [
                'username_or_mobile' => '9876543210',
                'password' => 'Secret@123',
                'email' => 'poster@example.com',
            ],
            'property_location' => [
                'property_village' => 'Auth Village',
            ],
        ]);

        $response->assertCreated();

        $copy = Property::query()->findOrFail($response->json('data.vizagland_copy_property_id'));

        $this->assertSame('Auth Village', $copy->property_village);
        $this->assertNull($copy->property_owner_email);
        $this->assertStringNotContainsString('Secret@123', $response->getContent());
    }

    public function test_authenticated_property_create_with_files(): void
    {
        Storage::fake('property_media');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/properties', [
            'registration_type' => 'Agent',
            'property_location' => [
                'property_village' => 'File Village',
            ],
            'property_images' => [
                UploadedFile::fake()->image('photo.jpg'),
            ],
            'property_documents' => [
                UploadedFile::fake()->create('deed.pdf', 100, 'application/pdf'),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated();

        $copyId = $response->json('data.vizagland_copy_property_id');
        $copy = Property::query()->with(['images', 'documents'])->findOrFail($copyId);

        $this->assertCount(1, $copy->images);
        $this->assertCount(1, $copy->documents);
        $this->assertSame($user->user_id, $copy->property_created_by);
    }

    /**
     * @return array<string, mixed>
     */
    private function samplePayload(): array
    {
        return [
            'registration_type' => 'Developer',
            'property_approval' => [
                'property_approval_authority' => 'GVMC',
            ],
            'property_location' => [
                'property_village' => 'Madhurawada',
                'property_nearby_location' => 'Bus Stand',
                'property_custom_nearby_location' => 'Near RTC',
                'property_district' => 'Visakhapatnam',
                'property_mandal' => 'Bheemunipatnam',
                'property_panchayati' => 'Madhurawada',
                'property_gvmc' => 'Zone 2',
                'property_vmrda' => 'VMRDA Area',
                'property_registration_area' => 'Visakhapatnam',
                'property_authority' => 'GVMC',
            ],
            'property_group_and_types' => [
                'property_residential_type' => 'Plot',
                'property_commercial_type' => 'Industrial Land',
                'property_development_type' => 'Gated Community',
                'property_layout_type' => 'Farm Plots',
                'property_construction_status' => 'Under Construction',
                'property_construction_type' => 'Individual House',
            ],
            'property_details' => [
                'property_project_name' => 'Sunrise Villas',
                'property_lp_no' => 'LP-123',
                'property_year' => 2020,
                'property_total_floors' => '',
                'property_block_phase' => 'Block A',
                'property_plot_no' => '12',
                'property_floor_number' => '2nd Floor',
                'property_facing' => 'North',
                'property_area' => 1452,
                'property_area_unit' => 'Sq.Yards',
                'property_price' => 2302302,
                'property_price_range' => '16 - 20 Lakhs',
                'property_age' => '5-10 Years',
                'property_bedrooms' => 2,
                'property_furnishing' => 'Semi-Furnished',
                'property_under' => 'Government',
                'property_document_no' => 'DOC-99',
                'property_document_year' => 2019,
                'property_registration_office_area' => 'Visakhapatnam',
            ],
            'property_auth' => [
                'username_or_mobile' => '9876543210',
                'password' => 'Secret@123',
                'email' => 'poster@example.com',
            ],
            'property_other_services' => [
                'property_youtube_video_link' => 'https://www.youtube.com/watch?v=abc123',
                'property_location_link' => 'https://maps.google.com/?q=17.7,83.2',
            ],
            'property_contact_numbers' => [
                ['registration_type' => 'Owner', 'phone_number' => '9876543210'],
                ['registration_type' => 'Agent', 'phone_number' => '9123456780'],
            ],
        ];
    }
}
