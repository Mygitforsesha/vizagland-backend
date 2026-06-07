<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyCreatedResource extends JsonResource
{
    /**
     * @param  array{property: Property, images_count: int, documents_count: int}  $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Property $property */
        $property = $this->resource['property'];

        return [
            'property_id' => $property->property_id,
            'property_status' => $property->property_status->value,
            'images_count' => $this->resource['images_count'],
            'documents_count' => $this->resource['documents_count'],
        ];
    }
}
