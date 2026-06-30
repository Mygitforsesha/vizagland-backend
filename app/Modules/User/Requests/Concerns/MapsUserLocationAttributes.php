<?php

namespace App\Modules\User\Requests\Concerns;

trait MapsUserLocationAttributes
{
    protected function normalizeLocationFieldAliases(): void
    {
        $aliases = [];

        $fieldAliases = [
            'user_nearby_location' => 'user_nearbyLocation',
            'user_custom_nearby_location' => 'user_customNearbyLocation',
            'user_gvmc_zone_ward_number' => 'user_gvmcZoneWardNumber',
            'user_registration_area' => 'user_registrationArea',
            'user_gvmc_vmrda' => 'user_gvmcVmrda',
        ];

        foreach ($fieldAliases as $snake => $camel) {
            if (! $this->has($snake) && $this->has($camel)) {
                $aliases[$snake] = $this->input($camel);
            }
        }

        if ($aliases !== []) {
            $this->merge($aliases);
        }
    }

    /**
     * @return list<string>
     */
    protected function locationFieldNames(): array
    {
        return [
            'user_latitude',
            'user_longitude',
            'user_road',
            'user_colony',
            'user_suburb',
            'user_village',
            'user_nearby_location',
            'user_custom_nearby_location',
            'user_mandal',
            'user_district',
            'user_panchayati',
            'user_gvmc_zone_ward_number',
            'user_vmrda',
            'user_registration_area',
            'user_gvmc_vmrda',
            'user_state',
            'user_pincode',
            'user_country',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function locationFieldRules(string $prefix = 'nullable'): array
    {
        return [
            'user_latitude' => [$prefix, 'numeric', 'between:-90,90'],
            'user_longitude' => [$prefix, 'numeric', 'between:-180,180'],
            'user_road' => [$prefix, 'string', 'max:255'],
            'user_colony' => [$prefix, 'string', 'max:255'],
            'user_suburb' => [$prefix, 'string', 'max:255'],
            'user_village' => [$prefix, 'string', 'max:255'],
            'user_nearby_location' => [$prefix, 'string', 'max:255'],
            'user_custom_nearby_location' => [$prefix, 'string', 'max:255'],
            'user_mandal' => [$prefix, 'string', 'max:255'],
            'user_district' => [$prefix, 'string', 'max:255'],
            'user_panchayati' => [$prefix, 'string', 'max:255'],
            'user_gvmc_zone_ward_number' => [$prefix, 'string', 'max:255'],
            'user_vmrda' => [$prefix, 'string', 'max:255'],
            'user_registration_area' => [$prefix, 'string', 'max:255'],
            'user_gvmc_vmrda' => [$prefix, 'string', 'max:255'],
            'user_state' => [$prefix, 'string', 'max:255'],
            'user_pincode' => [$prefix, 'string', 'max:20'],
            'user_country' => [$prefix, 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function locationProfileAttributes(): array
    {
        $attributes = [];

        foreach ($this->locationFieldNames() as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                continue;
            }

            $attributes[$field] = $value;
        }

        return $attributes;
    }
}
