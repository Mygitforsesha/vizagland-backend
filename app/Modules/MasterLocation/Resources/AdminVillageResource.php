<?php

namespace App\Modules\MasterLocation\Resources;

use App\Modules\MasterLocation\Models\MasterLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MasterLocation */
class AdminVillageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'master_location_id' => $this->master_location_id,
            'master_location_village' => $this->master_location_village,
            'master_location_nearby_location' => $this->master_location_nearby_location,
            'master_location_additional_nearby_location' => $this->master_location_additional_nearby_location,
            'master_location_district' => $this->master_location_district,
            'master_location_mandal' => $this->master_location_mandal,
            'master_location_panchayati' => $this->master_location_panchayati,
            'master_location_gvmc_zone' => $this->master_location_gvmc_zone,
            'master_location_gvmc_ward' => $this->master_location_gvmc_ward,
            'master_location_vmrda' => $this->master_location_vmrda,
            'master_location_registration_office' => $this->master_location_registration_office,
            'master_location_authority' => $this->master_location_authority,
            'master_location_created_at' => $this->master_location_created_at?->toIso8601String(),
            'master_location_updated_at' => $this->master_location_updated_at?->toIso8601String(),
        ];
    }
}
