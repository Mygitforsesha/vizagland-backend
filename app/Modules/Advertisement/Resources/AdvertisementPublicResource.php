<?php

namespace App\Modules\Advertisement\Resources;

use App\Modules\Advertisement\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Advertisement */
class AdvertisementPublicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'advertisement_id' => $this->advertisement_id,
            'advertisement_title' => $this->advertisement_title,
            'advertisement_type' => $this->advertisement_type->value,
            'advertisement_image_url' => Storage::disk('public')->url($this->advertisement_image_path),
            'advertisement_redirect_url' => $this->advertisement_redirect_url,
            'advertisement_display_order' => $this->advertisement_display_order,
        ];
    }
}
