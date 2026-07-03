<?php

namespace App\Modules\ActivityLog\Resources;

use App\Modules\ActivityLog\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ActivityLog */
class ActivityLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'activity_log_id' => $this->activity_log_id,
            'activity_log_user_id' => $this->activity_log_user_id,
            'activity_log_user_name' => $this->activity_log_user_name,
            'activity_log_user_role' => $this->activity_log_user_role,
            'activity_log_type' => $this->activity_log_type->value,
            'activity_log_type_label' => $this->activity_log_type->label(),
            'activity_log_action' => $this->activity_log_action,
            'activity_log_action_label' => $this->actionLabel(),
            'activity_log_description' => $this->activity_log_description,
            'activity_log_entity_type' => $this->activity_log_entity_type,
            'activity_log_entity_id' => $this->activity_log_entity_id,
            'activity_log_metadata' => $this->activity_log_metadata,
            'activity_log_ip_address' => $this->activity_log_ip_address,
            'activity_log_latitude' => $this->resolvedLocationValue('latitude'),
            'activity_log_longitude' => $this->resolvedLocationValue('longitude'),
            'activity_log_road' => $this->resolvedLocationValue('road'),
            'activity_log_colony' => $this->resolvedLocationValue('colony'),
            'activity_log_suburb' => $this->resolvedLocationValue('suburb'),
            'activity_log_village' => $this->resolvedLocationValue('village'),
            'activity_log_mandal' => $this->resolvedLocationValue('mandal'),
            'activity_log_district' => $this->resolvedLocationValue('district'),
            'activity_log_state' => $this->resolvedLocationValue('state'),
            'activity_log_pincode' => $this->resolvedLocationValue('pincode'),
            'activity_log_country' => $this->resolvedLocationValue('country'),
            'activity_log_location_label' => $this->locationLabel(),
            'activity_log_google_maps_url' => $this->googleMapsUrl(),
            'activity_log_location_source' => $this->locationSource(),
            'activity_log_created_at' => $this->activity_log_created_at?->toIso8601String(),
        ];
    }
}
