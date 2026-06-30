<?php

namespace App\Modules\MasterLocation\Resources;

use App\Modules\MasterLocation\Models\MasterLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MasterLocation */
class MasterLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->master_location_id,
            'village' => $this->master_location_village,
            'nearby_location' => $this->master_location_nearby_location ?? '',
            'additional_nearby_location' => $this->master_location_additional_nearby_location ?? '',
            'district' => $this->master_location_district ?? '',
            'mandal' => $this->master_location_mandal ?? '',
            'panchayati' => $this->master_location_panchayati ?? '',
            'gvmc_zone' => $this->master_location_gvmc_zone ?? '',
            'gvmc_ward' => $this->master_location_gvmc_ward ?? '',
            'vmrda' => $this->master_location_vmrda ?? '',
            'registration_office' => $this->master_location_registration_office ?? '',
            'authority' => $this->master_location_authority ?? '',
            'custom_nearby_location' => null,
        ];
    }
}
