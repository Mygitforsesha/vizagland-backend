<?php

namespace App\Modules\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDashboardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'dashboard_summary' => $this->resource['dashboard_summary'],
            'property_statistics' => $this->resource['property_statistics'],
            'user_statistics' => $this->resource['user_statistics'],
            'verification_statistics' => $this->resource['verification_statistics'],
            'property_posting_trend' => $this->resource['property_posting_trend'],
            'user_activity' => $this->resource['user_activity'],
            'recent_activity' => $this->resource['recent_activity'],
            'duplicate_statistics' => $this->resource['duplicate_statistics'],
        ];
    }
}
