<?php

namespace App\Modules\OtherService\Resources;

use App\Modules\OtherService\Models\OtherServiceWhatsappNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OtherServiceWhatsappNumber */
class OtherServiceWhatsappNumberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'other_service_whatsapp_number_id' => $this->other_service_whatsapp_number_id,
            'other_service_whatsapp_number' => $this->other_service_whatsapp_number,
            'other_service_whatsapp_number_is_primary' => $this->other_service_whatsapp_number_is_primary,
            'other_service_whatsapp_number_sort_order' => $this->other_service_whatsapp_number_sort_order,
        ];
    }
}
