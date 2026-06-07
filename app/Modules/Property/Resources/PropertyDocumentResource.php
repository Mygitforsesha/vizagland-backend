<?php

namespace App\Modules\Property\Resources;

use App\Modules\Property\Models\PropertyDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin PropertyDocument
 */
class PropertyDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_document_id' => $this->property_document_id,
            'property_id' => $this->property_id,
            'property_document_name' => $this->property_document_name,
            'property_document_type' => $this->property_document_type,
            'property_document_path' => $this->property_document_path,
            'property_document_url' => Storage::disk('public')->url($this->property_document_path),
            'property_document_size' => $this->property_document_size,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
