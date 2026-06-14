<?php

namespace App\Modules\Report\Resources;

use App\Modules\Report\Models\ExportJob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ExportJob */
class ExportJobResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'export_job_id' => $this->export_job_id,
            'export_job_type' => $this->export_job_type->value,
            'export_job_type_label' => $this->export_job_type->label(),
            'export_job_format' => $this->export_job_format->value,
            'export_job_format_label' => $this->export_job_format->label(),
            'export_job_status' => $this->export_job_status->value,
            'export_job_status_label' => $this->export_job_status->label(),
            'export_job_file_name' => $this->export_job_file_name,
            'export_job_file_size' => $this->export_job_file_size,
            'export_job_created_at' => $this->export_job_created_at?->toIso8601String(),
            'export_job_completed_at' => $this->export_job_completed_at?->toIso8601String(),
            'generated_by_user' => $this->when(
                $this->relationLoaded('user') && $this->user !== null,
                fn () => [
                    'user_id' => $this->user->user_id,
                    'user_full_name' => $this->user->user_full_name,
                ],
            ),
        ];
    }
}
