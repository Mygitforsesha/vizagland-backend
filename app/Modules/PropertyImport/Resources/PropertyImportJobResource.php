<?php

namespace App\Modules\PropertyImport\Resources;

use App\Modules\PropertyImport\Models\PropertyImportJob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PropertyImportJob */
class PropertyImportJobResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'property_import_job_id' => $this->property_import_job_id,
            'property_import_file_name' => $this->property_import_file_name,
            'property_import_total_rows' => $this->property_import_total_rows,
            'property_import_success_rows' => $this->property_import_success_rows,
            'property_import_failed_rows' => $this->property_import_failed_rows,
            'property_import_status' => $this->property_import_status->value,
            'property_import_status_label' => $this->property_import_status->label(),
            'property_import_started_at' => $this->property_import_started_at?->toIso8601String(),
            'property_import_completed_at' => $this->property_import_completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by_user' => $this->when(
                $this->relationLoaded('createdBy') && $this->createdBy !== null,
                fn () => [
                    'user_id' => $this->createdBy->user_id,
                    'user_full_name' => $this->createdBy->user_full_name,
                ],
            ),
            'property_import_errors' => PropertyImportErrorResource::collection(
                $this->whenLoaded('errors'),
            ),
        ];
    }
}
