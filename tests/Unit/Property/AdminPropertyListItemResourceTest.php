<?php

namespace Tests\Unit\Property;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Resources\AdminPropertyListItemResource;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminPropertyListItemResourceTest extends TestCase
{
    public function test_original_property_uses_created_by_user_for_cadre(): void
    {
        $poster = new User([
            'user_full_name' => 'Sasi Pampana',
            'user_phone' => '9581160834',
        ]);
        $poster->user_id = 10;

        $property = new Property([
            'property_record_type' => PropertyRecordType::Original,
            'property_status' => PropertyStatus::Draft,
            'property_created_by' => 10,
        ]);
        $property->setRelation('createdBy', $poster);

        $payload = (new AdminPropertyListItemResource($property))->toArray(Request::create('/'));

        $this->assertSame([
            'property_posted_by' => 'Sasi Pampana',
            'property_posted_phone_number' => '9581160834',
        ], $payload['property_cadre']);
    }

    public function test_vizagland_copy_uses_own_created_by_when_present(): void
    {
        $poster = new User([
            'user_full_name' => 'Sasi Pampana',
            'user_phone' => '9581160834',
        ]);
        $poster->user_id = 10;

        $property = new Property([
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_created_by' => 10,
            'property_parent_property_id' => 1,
        ]);
        $property->setRelation('createdBy', $poster);
        $property->setRelation('parentProperty', new Property(['property_id' => 1]));

        $payload = (new AdminPropertyListItemResource($property))->toArray(Request::create('/'));

        $this->assertSame('Sasi Pampana', $payload['property_cadre']['property_posted_by']);
    }

    public function test_vizagland_copy_falls_back_to_parent_creator_when_missing(): void
    {
        $poster = new User([
            'user_full_name' => 'Sasi Pampana',
            'user_phone' => '9581160834',
        ]);
        $poster->user_id = 10;

        $parent = new Property([
            'property_id' => 1,
            'property_created_by' => 10,
        ]);
        $parent->setRelation('createdBy', $poster);

        $property = new Property([
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
            'property_created_by' => null,
            'property_parent_property_id' => 1,
        ]);
        $property->setRelation('createdBy', null);
        $property->setRelation('parentProperty', $parent);

        $payload = (new AdminPropertyListItemResource($property))->toArray(Request::create('/'));

        $this->assertSame([
            'property_posted_by' => 'Sasi Pampana',
            'property_posted_phone_number' => '9581160834',
        ], $payload['property_cadre']);
    }

    public function test_missing_poster_returns_null_cadre(): void
    {
        $property = new Property([
            'property_record_type' => PropertyRecordType::Original,
            'property_status' => PropertyStatus::Draft,
            'property_created_by' => null,
        ]);
        $property->setRelation('createdBy', null);

        $payload = (new AdminPropertyListItemResource($property))->toArray(Request::create('/'));

        $this->assertSame([
            'property_posted_by' => null,
            'property_posted_phone_number' => null,
        ], $payload['property_cadre']);
    }

    public function test_existing_list_fields_remain_present(): void
    {
        $property = new Property([
            'property_id' => 85,
            'property_reference_id' => 'VL-TEST1234',
            'property_record_type' => PropertyRecordType::VizaglandCopy,
            'property_status' => PropertyStatus::Draft,
        ]);
        $property->setRelation('createdBy', null);

        $payload = (new AdminPropertyListItemResource($property))->toArray(Request::create('/'));

        $this->assertArrayHasKey('property_id', $payload);
        $this->assertArrayHasKey('property_reference_id', $payload);
        $this->assertArrayHasKey('property_created_by_user_id', $payload);
        $this->assertArrayHasKey('property_cadre', $payload);
    }
}
