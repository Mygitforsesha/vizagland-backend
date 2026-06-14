<?php

namespace App\Modules\ActivityLog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ActivityLog\Models\ActivityLog;
use App\Modules\ActivityLog\Requests\ListActivityLogsRequest;
use App\Modules\ActivityLog\Resources\ActivityLogListResource;
use App\Modules\ActivityLog\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(ListActivityLogsRequest $request): JsonResponse
    {
        $logs = $this->activityLogService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new ActivityLogListResource($logs),
            message: 'Activity logs retrieved successfully.',
        );
    }

    public function export(ListActivityLogsRequest $request): StreamedResponse
    {
        $logs = $this->activityLogService->export($request->filters());
        $filename = 'activity-logs-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'activity_log_id',
                'activity_log_user_name',
                'activity_log_user_role',
                'activity_log_type',
                'activity_log_action',
                'activity_log_description',
                'activity_log_entity_type',
                'activity_log_entity_id',
                'activity_log_ip_address',
                'activity_log_created_at',
            ]);

            foreach ($logs as $log) {
                /** @var ActivityLog $log */
                fputcsv($handle, [
                    $log->activity_log_id,
                    $log->activity_log_user_name,
                    $log->activity_log_user_role,
                    $log->activity_log_type->value,
                    $log->activity_log_action,
                    $log->activity_log_description,
                    $log->activity_log_entity_type,
                    $log->activity_log_entity_id,
                    $log->activity_log_ip_address,
                    $log->activity_log_created_at?->toIso8601String(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
