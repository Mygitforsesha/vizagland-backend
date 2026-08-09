<?php

namespace Tests\Feature\Property;

use App\Modules\Property\Enums\PropertyCreatedByType;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertySource;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPropertiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_properties_returns_public_user_posts_by_phone(): void
    {
        $user = User::factory()->create([
            'user_phone' => '9632587410',
            'user_role' => UserRole::PublicUser,
            'user_is_active' => true,
        ]);

        $mine = Property::query()->create([
            'property_reference_id' => 'VG-MINE-1',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_source' => PropertySource::Public,
            'property_created_by_type' => PropertyCreatedByType::Public,
            'property_created_by' => $user->user_id,
            'property_created_by_id' => $user->user_id,
            'property_village' => 'Bheemunipatnam',
            'property_is_deleted' => false,
        ]);

        Property::query()->create([
            'property_reference_id' => 'VG-OTHER-1',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_source' => PropertySource::Public,
            'property_created_by_type' => PropertyCreatedByType::Public,
            'property_created_by' => null,
            'property_created_by_id' => null,
            'property_village' => 'Other',
            'property_is_deleted' => false,
        ]);

        $response = $this->getJson('/api/properties/my-properties?phone_number=9632587410');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.property_id', $mine->property_id)
            ->assertJsonPath('data.items.0.property_village', 'Bheemunipatnam')
            ->assertJsonPath('data.items.0.property_status', PropertyStatus::Draft->value)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        [
                            'property_id',
                            'property_reference_id',
                            'property_record_type',
                            'property_status',
                            'property_source',
                            'property_village',
                            'property_district',
                            'property_mandal',
                            'property_owner_name',
                            'property_owner_phone',
                            'property_price',
                            'property_area',
                            'property_area_unit',
                            'property_images',
                            'property_documents',
                            'property_contact_numbers',
                            'images_count',
                            'documents_count',
                            'property_created_at',
                            'property_updated_at',
                        ],
                    ],
                ],
            ]);
    }

    public function test_my_properties_returns_agent_posts_by_phone(): void
    {
        $agent = User::factory()->create([
            'user_phone' => '7878752525',
            'user_role' => UserRole::Agent,
            'user_is_active' => true,
        ]);

        $mine = Property::query()->create([
            'property_reference_id' => 'VG-AGENT-1',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_source' => PropertySource::Agent,
            'property_created_by_type' => PropertyCreatedByType::Agent,
            'property_created_by' => $agent->user_id,
            'property_created_by_id' => $agent->user_id,
            'property_village' => 'Agent Village',
            'property_is_deleted' => false,
        ]);

        $response = $this->getJson('/api/properties/my-properties?phone_number=7878752525');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.property_id', $mine->property_id);
    }

    public function test_my_properties_returns_agent_public_posts_by_owner_phone_when_unlinked(): void
    {
        User::factory()->create([
            'user_phone' => '7878752525',
            'user_role' => UserRole::Agent,
            'user_is_active' => true,
        ]);

        $mine = Property::query()->create([
            'property_reference_id' => 'VG-AGENT-OWNER-1',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_source' => PropertySource::Public,
            'property_created_by_type' => PropertyCreatedByType::Public,
            'property_created_by' => null,
            'property_created_by_id' => null,
            'property_owner_phone' => '7878752525',
            'property_village' => 'Owner Phone Village',
            'property_is_deleted' => false,
        ]);

        Property::query()->create([
            'property_reference_id' => 'VG-OTHER-OWNER-1',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_source' => PropertySource::Public,
            'property_created_by_type' => PropertyCreatedByType::Public,
            'property_created_by' => null,
            'property_created_by_id' => null,
            'property_owner_phone' => '9632587410',
            'property_village' => 'Other',
            'property_is_deleted' => false,
        ]);

        $response = $this->getJson('/api/properties/my-properties?phone_number=7878752525');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.property_id', $mine->property_id);
    }

    public function test_my_properties_returns_empty_when_phone_unknown(): void
    {
        $response = $this->getJson('/api/properties/my-properties?phone_number=9632587410');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.items', []);
    }

    public function test_my_properties_matches_owner_phone_without_user_role_restriction(): void
    {
        $mine = Property::query()->create([
            'property_reference_id' => 'VG-OWNER-PHONE-ONLY-1',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_source' => PropertySource::Public,
            'property_created_by_type' => PropertyCreatedByType::Public,
            'property_created_by' => null,
            'property_created_by_id' => null,
            'property_owner_phone' => '4564564561',
            'property_village' => 'Phone Match Village',
            'property_is_deleted' => false,
        ]);

        $response = $this->getJson('/api/properties/my-properties?phone_number=4564564561');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.property_id', $mine->property_id);
    }

    public function test_my_properties_requires_phone_number(): void
    {
        $response = $this->getJson('/api/properties/my-properties');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_my_properties_accepts_any_phone_number_format(): void
    {
        $response = $this->getJson('/api/properties/my-properties?phone_number=4564564561');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.items', []);
    }
}
