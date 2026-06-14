<?php

namespace App\Modules\Report\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Notification\Enums\NotificationType;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Report\Enums\ExportFormat;
use App\Modules\Report\Enums\ExportJobStatus;
use App\Modules\Report\Enums\ExportType;
use App\Modules\Report\Exports\ExportDataProvider;
use App\Modules\Report\Exports\ExportFileGenerator;
use App\Modules\Report\Models\ExportJob;
use App\Modules\Report\Repositories\ExportJobRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ExportService
{
    public function __construct(
        private readonly ExportJobRepository $exportJobRepository,
        private readonly ExportDataProvider $exportDataProvider,
        private readonly ExportFileGenerator $exportFileGenerator,
        private readonly ActivityLogService $activityLogService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function create(User $user, ExportType $type, ExportFormat $format, array $filters = []): ExportJob
    {
        $exportJob = $this->exportJobRepository->create([
            'export_job_user_id' => $user->user_id,
            'export_job_type' => $type,
            'export_job_format' => $format,
            'export_job_status' => ExportJobStatus::Pending,
            'export_job_filters' => $filters,
        ]);

        try {
            return DB::transaction(function () use ($exportJob, $user, $type, $format, $filters) {
                $this->exportJobRepository->update($exportJob, [
                    'export_job_status' => ExportJobStatus::Processing,
                ]);

                $dataset = $this->exportDataProvider->dataset($type, $filters);
                $fileName = $this->buildFileName($type, $format);
                $relativePath = 'exports/'.$fileName;
                $absolutePath = Storage::disk('local')->path($relativePath);

                Storage::disk('local')->makeDirectory('exports');
                $this->exportFileGenerator->generate(
                    path: $absolutePath,
                    format: $format,
                    headers: $dataset['headers'],
                    rows: $dataset['rows'],
                    title: $dataset['title'],
                );

                $fileSize = filesize($absolutePath) ?: 0;

                $completedJob = $this->exportJobRepository->update($exportJob, [
                    'export_job_status' => ExportJobStatus::Completed,
                    'export_job_file_name' => $fileName,
                    'export_job_file_path' => $relativePath,
                    'export_job_file_size' => $fileSize,
                    'export_job_completed_at' => now(),
                ]);

                $this->activityLogService->log(
                    type: ActivityLogType::Report,
                    action: 'generated',
                    description: "Generated {$type->label()} export ({$format->label()})",
                    entityType: 'export_job',
                    entityId: $completedJob->export_job_id,
                    user: $user,
                    metadata: [
                        'export_type' => $type->value,
                        'export_format' => $format->value,
                        'export_job_file_name' => $fileName,
                    ],
                );

                $this->notificationService->notifyUser(
                    userId: $user->user_id,
                    type: NotificationType::ReportGenerated,
                    title: 'Export Ready',
                    message: "Your {$type->label()} export ({$format->label()}) is ready for download.",
                );

                return $completedJob;
            });
        } catch (Throwable $exception) {
            $this->exportJobRepository->update($exportJob, [
                'export_job_status' => ExportJobStatus::Failed,
                'export_job_error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function listForUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->exportJobRepository->paginateForUser($userId, $perPage);
    }

    public function downloadPath(int $exportJobId, int $userId): ExportJob
    {
        $exportJob = $this->exportJobRepository->findForUser($exportJobId, $userId);

        if ($exportJob === null) {
            throw (new ModelNotFoundException)->setModel(ExportJob::class, [$exportJobId]);
        }

        if ($exportJob->export_job_status !== ExportJobStatus::Completed || $exportJob->export_job_file_path === null) {
            throw new \RuntimeException('Export file is not available for download.');
        }

        if (! Storage::disk('local')->exists($exportJob->export_job_file_path)) {
            throw new \RuntimeException('Export file no longer exists.');
        }

        return $exportJob;
    }

    private function buildFileName(ExportType $type, ExportFormat $format): string
    {
        return sprintf(
            '%s-%s-%s.%s',
            $type->value,
            now()->format('Ymd-His'),
            Str::lower(Str::random(6)),
            $format->extension(),
        );
    }
}
