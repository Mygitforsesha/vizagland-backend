<?php

namespace App\Modules\ActivityLog\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Models\ActivityLog;
use App\Modules\ActivityLog\Repositories\ActivityLogRepository;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyReviewLog;
use App\Modules\Report\Enums\ExportJobStatus;
use App\Modules\Report\Models\ExportJob;
use App\Modules\User\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ActivityLogBackfillService
{
    public function __construct(
        private readonly ActivityLogRepository $activityLogRepository,
    ) {}

    public function backfill(): int
    {
        $created = 0;
        $created += $this->backfillPropertyActivities();
        $created += $this->backfillPropertyReviewLogs();
        $created += $this->backfillExportJobs();
        $created += $this->backfillUserLogins();

        return $created;
    }

    private function backfillPropertyActivities(): int
    {
        $created = 0;

        Property::query()
            ->with(['createdBy:user_id,user_full_name,user_role'])
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->orderBy('property_id')
            ->chunkById(100, function ($properties) use (&$created): void {
                foreach ($properties as $property) {
                    $referenceId = $property->property_reference_id ?? (string) $property->property_id;
                    $user = $property->createdBy;

                    if ($this->insertIfMissing(
                        type: ActivityLogType::Property,
                        action: 'created',
                        description: "Created property {$referenceId}",
                        entityType: 'property',
                        entityId: $property->property_id,
                        user: $user,
                        metadata: ['property_reference_id' => $referenceId],
                        createdAt: $property->created_at,
                    )) {
                        $created++;
                    }

                    if ($property->property_submitted_at !== null
                        && $this->insertIfMissing(
                            type: ActivityLogType::PropertyReview,
                            action: 'submitted_for_review',
                            description: "Submitted property {$referenceId} for review",
                            entityType: 'property',
                            entityId: $property->property_id,
                            user: $user,
                            metadata: ['property_reference_id' => $referenceId],
                            createdAt: $property->property_submitted_at,
                        )) {
                        $created++;
                    }
                }
            }, 'property_id');

        return $created;
    }

    private function backfillPropertyReviewLogs(): int
    {
        if (! Schema::hasTable('property_review_logs')) {
            return 0;
        }

        $created = 0;

        PropertyReviewLog::query()
            ->with(['performedBy:user_id,user_full_name,user_role'])
            ->orderBy('property_review_log_id')
            ->chunkById(100, function ($logs) use (&$created): void {
                foreach ($logs as $log) {
                    $property = Property::query()
                        ->select(['property_id', 'property_reference_id'])
                        ->find($log->property_id);

                    if ($property === null) {
                        continue;
                    }

                    $referenceId = $property->property_reference_id ?? (string) $property->property_id;
                    $action = $log->property_review_action->value;
                    $label = $log->property_review_action->label();

                    if ($this->insertIfMissing(
                        type: ActivityLogType::PropertyReview,
                        action: $action,
                        description: "{$label} property {$referenceId}",
                        entityType: 'property',
                        entityId: $property->property_id,
                        user: $log->performedBy,
                        metadata: ['property_reference_id' => $referenceId],
                        createdAt: $log->property_review_created_at,
                    )) {
                        $created++;
                    }
                }
            }, 'property_review_log_id');

        return $created;
    }

    private function backfillExportJobs(): int
    {
        if (! Schema::hasTable('export_jobs')) {
            return 0;
        }

        $created = 0;

        ExportJob::query()
            ->with(['user:user_id,user_full_name,user_role'])
            ->where('export_job_status', ExportJobStatus::Completed)
            ->orderBy('export_job_id')
            ->chunkById(100, function ($jobs) use (&$created): void {
                foreach ($jobs as $job) {
                    if ($this->insertIfMissing(
                        type: ActivityLogType::Report,
                        action: 'generated',
                        description: "Generated {$job->export_job_type->label()} export ({$job->export_job_format->label()})",
                        entityType: 'export_job',
                        entityId: $job->export_job_id,
                        user: $job->user,
                        metadata: [
                            'export_type' => $job->export_job_type->value,
                            'export_format' => $job->export_job_format->value,
                            'export_job_file_name' => $job->export_job_file_name,
                        ],
                        createdAt: $job->export_job_completed_at ?? $job->export_job_created_at,
                    )) {
                        $created++;
                    }
                }
            }, 'export_job_id');

        return $created;
    }

    private function backfillUserLogins(): int
    {
        if (! Schema::hasColumn('users', 'user_last_login_at')) {
            return 0;
        }

        $created = 0;

        User::query()
            ->whereNotNull('user_last_login_at')
            ->orderBy('user_id')
            ->chunkById(100, function ($users) use (&$created): void {
                foreach ($users as $user) {
                    if ($this->insertIfMissing(
                        type: ActivityLogType::Authentication,
                        action: 'login',
                        description: "User logged in: {$user->user_full_name}",
                        entityType: 'user',
                        entityId: $user->user_id,
                        user: $user,
                        metadata: null,
                        createdAt: $user->user_last_login_at,
                    )) {
                        $created++;
                    }
                }
            }, 'user_id');

        return $created;
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function insertIfMissing(
        ActivityLogType $type,
        string $action,
        string $description,
        string $entityType,
        int $entityId,
        ?User $user,
        ?array $metadata,
        ?Carbon $createdAt,
    ): bool {
        if ($this->activityLogRepository->existsForEntityAction($entityType, $entityId, $action)) {
            return false;
        }

        $this->activityLogRepository->create([
            'activity_log_user_id' => $user?->user_id,
            'activity_log_user_name' => $user?->user_full_name,
            'activity_log_user_role' => $user?->user_role?->value,
            'activity_log_type' => $type->value,
            'activity_log_action' => $action,
            'activity_log_description' => $description,
            'activity_log_entity_type' => $entityType,
            'activity_log_entity_id' => $entityId,
            'activity_log_metadata' => $metadata,
            'activity_log_created_at' => $createdAt ?? now(),
        ]);

        return true;
    }
}
