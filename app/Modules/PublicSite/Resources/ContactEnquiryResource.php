<?php

namespace App\Modules\PublicSite\Resources;

use App\Modules\PublicSite\Models\ContactEnquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContactEnquiry
 */
class ContactEnquiryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contact_enquiry_id' => $this->contact_enquiry_id,
            'contact_enquiry_status' => $this->contact_enquiry_status?->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
