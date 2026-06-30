<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyDocument;
use App\Modules\Property\Services\PropertyMediaStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyDocument
 */
class PropertyDetailsDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_document_id' => $this->property_document_id,
            'property_document_original_name' => $this->property_document_original_name,
            'property_document_url' => app(PropertyMediaStorage::class)->url($this->property_document_path),
            'property_document_size' => $this->property_document_size,
            'property_document_mime_type' => $this->property_document_mime_type,
        ];
    }
}
