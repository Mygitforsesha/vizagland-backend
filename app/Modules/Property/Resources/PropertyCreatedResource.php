<?php

namespace App\Modules\Property\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyCreatedResource extends JsonResource
{
    /**
     * @param  array{
     *     original_property: \App\Modules\Property\Models\Property,
     *     vizagland_copy_property: \App\Modules\Property\Models\Property,
     *     property_reference_id: string,
     *     images_count: int,
     *     documents_count: int
     * }  $resource
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
        return [
            'original_property_id' => $this->resource['original_property']->property_id,
            'vizagland_copy_property_id' => $this->resource['vizagland_copy_property']->property_id,
            'property_reference_id' => $this->resource['property_reference_id'],
        ];
    }
}
