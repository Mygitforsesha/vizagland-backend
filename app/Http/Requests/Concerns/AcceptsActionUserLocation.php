<?php

namespace App\Http\Requests\Concerns;

trait AcceptsActionUserLocation
{
    /**
     * Action-time device GPS fields sent by the public frontend.
     *
     * @return list<string>
     */
    protected function actionUserLocationFieldNames(): array
    {
        return [
            'user_latitude',
            'user_longitude',
            'user_road',
            'user_colony',
            'user_suburb',
            'user_village',
            'user_mandal',
            'user_district',
            'user_state',
            'user_pincode',
            'user_country',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function actionUserLocationRules(): array
    {
        return [
            'user_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'user_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'user_road' => ['nullable', 'string', 'max:255'],
            'user_colony' => ['nullable', 'string', 'max:255'],
            'user_suburb' => ['nullable', 'string', 'max:255'],
            'user_village' => ['nullable', 'string', 'max:255'],
            'user_mandal' => ['nullable', 'string', 'max:255'],
            'user_district' => ['nullable', 'string', 'max:255'],
            'user_state' => ['nullable', 'string', 'max:255'],
            'user_pincode' => ['nullable', 'string', 'max:20'],
            'user_country' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Non-empty action-time location payload, or null when GPS was unavailable/denied.
     *
     * @return array<string, mixed>|null
     */
    public function actionUserLocationAttributes(): ?array
    {
        $attributes = [];

        foreach ($this->actionUserLocationFieldNames() as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);

                if ($value === '') {
                    continue;
                }
            }

            $attributes[$field] = $value;
        }

        return $attributes === [] ? null : $attributes;
    }
}
