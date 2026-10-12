<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyOwner;
use App\Modules\Property\Support\DefaultPropertyOwners;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyOwner
 */
class PropertyOwnerResource extends JsonResource
{
    /**
     * Saved owners, or the two VizagLand contacts when a property has none yet.
     *
     * @return list<array{property_owner_name: ?string, property_owner_phone: ?string, property_owner_email: ?string}>
     */
    public static function listFor(Property $property): array
    {
        $owners = $property->relationLoaded('owners')
            ? $property->owners
            : $property->owners()->get();

        if ($owners->isNotEmpty()) {
            return $owners
                ->map(static fn (PropertyOwner $owner): array => [
                    'property_owner_name' => $owner->property_owner_name,
                    'property_owner_phone' => $owner->property_owner_phone,
                    'property_owner_email' => $owner->property_owner_email,
                ])
                ->values()
                ->all();
        }

        return DefaultPropertyOwners::list();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_owner_name' => $this->property_owner_name,
            'property_owner_phone' => $this->property_owner_phone,
            'property_owner_email' => $this->property_owner_email,
        ];
    }
}
