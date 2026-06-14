<?php

namespace App\Modules\Report\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportsDashboardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'],
            'daily_activity' => $this->resource['daily_activity'],
            'user_reports' => $this->resource['user_reports'],
            'property_reports' => $this->resource['property_reports'],
            'duplicate_reports' => $this->resource['duplicate_reports'],
        ];
    }
}
