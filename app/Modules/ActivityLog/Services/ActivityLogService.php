<?php

namespace App\Modules\ActivityLog\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Models\ActivityLog;
use App\Modules\ActivityLog\Repositories\ActivityLogRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function __construct(
        private readonly ActivityLogRepository $activityLogRepository,
        private readonly ActivityLogBackfillService $activityLogBackfillService,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        ActivityLogType|string $type,
        string $action,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?User $user = null,
        ?array $metadata = null,
    ): ActivityLog {
        $resolvedUser = $user ?? Auth::user();
        $request = $this->resolveRequest();

        $typeValue = $type instanceof ActivityLogType ? $type->value : $type;

        return $this->activityLogRepository->create([
            'activity_log_user_id' => $resolvedUser?->user_id,
            'activity_log_user_name' => $resolvedUser?->user_full_name,
            'activity_log_user_role' => $resolvedUser?->user_role?->value,
            'activity_log_type' => $typeValue,
            'activity_log_action' => $action,
            'activity_log_description' => $description,
            'activity_log_entity_type' => $entityType,
            'activity_log_entity_id' => $entityId,
            'activity_log_ip_address' => $request?->ip(),
            'activity_log_user_agent' => $request?->userAgent(),
            'activity_log_metadata' => $metadata,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->activityLogRepository->paginate($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): \Illuminate\Support\LazyCollection
    {
        return $this->activityLogRepository->exportCursor($filters);
    }

    public function backfillHistoricalActivities(): int
    {
        return $this->activityLogBackfillService->backfill();
    }

    private function resolveRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }
}
