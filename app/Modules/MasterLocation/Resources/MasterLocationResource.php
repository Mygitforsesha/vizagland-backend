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
            'display_label' => $this->displayLabel(),
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

    private function displayLabel(): string
    {
        $parts = collect([
            $this->master_location_village,
            $this->master_location_panchayati,
            $this->master_location_mandal,
            $this->master_location_district,
        ])
            ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->unique(static fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();

        return implode(', ', $parts);
    }
}
