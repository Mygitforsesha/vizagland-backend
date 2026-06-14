<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'property_document_url' => Storage::disk('public')->url($this->property_document_path),
            'property_document_size' => $this->property_document_size,
            'property_document_mime_type' => $this->property_document_mime_type,
        ];
    }
}
