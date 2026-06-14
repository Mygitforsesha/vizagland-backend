<?php

namespace App\Modules\PropertyFieldConfiguration\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicPropertyFormConfigResource extends JsonResource
{
    /**
     * @return array<string, list<array{
     *     property_field_key: string,
     *     property_field_label: string,
     *     property_field_data_type: string,
     *     property_field_is_required: bool
     * }>>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
