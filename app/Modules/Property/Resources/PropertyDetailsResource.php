<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyDetailsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property' => new PropertyDetailResource($this->resource),
            'images' => PropertyImageResource::collection($this->resource->images),
            'documents' => PropertyDocumentResource::collection($this->resource->documents),
            'reviews' => PropertyReviewResource::collection($this->resource->reviews),
        ];
    }
}
