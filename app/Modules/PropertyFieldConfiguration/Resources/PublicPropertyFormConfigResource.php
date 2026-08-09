<?php

namespace App\Modules\PropertyFieldConfiguration\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicPropertyFormConfigResource extends JsonResource
{
    /**
     * @return array{sections: list<array<string, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
