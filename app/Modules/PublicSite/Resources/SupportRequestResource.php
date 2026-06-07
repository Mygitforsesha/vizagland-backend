<?php

namespace App\Modules\PublicSite\Resources;

use App\Modules\PublicSite\Models\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportRequest
 */
class SupportRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'support_request_id' => $this->support_request_id,
            'support_request_status' => $this->support_request_status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
