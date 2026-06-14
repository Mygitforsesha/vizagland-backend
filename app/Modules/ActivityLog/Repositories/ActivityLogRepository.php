<?php

namespace App\Modules\ActivityLog\Repositories;

use App\Modules\ActivityLog\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class ActivityLogRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->filteredQuery($filters);

        return $query
            ->orderByDesc('activity_log_created_at')
            ->orderByDesc('activity_log_id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LazyCollection<int, ActivityLog>
     */
    public function exportCursor(array $filters): LazyCollection
    {
        return $this->filteredQuery($filters)
            ->orderByDesc('activity_log_created_at')
            ->orderByDesc('activity_log_id')
            ->cursor();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ActivityLog
    {
        return ActivityLog::query()->create($attributes);
    }

    public function count(): int
    {
        return ActivityLog::query()->count();
    }

    public function existsForEntityAction(string $entityType, int $entityId, string $action): bool
    {
        return ActivityLog::query()
            ->where('activity_log_entity_type', $entityType)
            ->where('activity_log_entity_id', $entityId)
            ->where('activity_log_action', $action)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<ActivityLog>
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = ActivityLog::query();

        if (! empty($filters['activity_log_type'])) {
            $query->where('activity_log_type', $filters['activity_log_type']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('activity_log_user_id', $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('activity_log_created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('activity_log_created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('activity_log_user_name', 'like', '%'.$search.'%')
                    ->orWhere('activity_log_action', 'like', '%'.$search.'%')
                    ->orWhere('activity_log_type', 'like', '%'.$search.'%')
                    ->orWhere('activity_log_description', 'like', '%'.$search.'%')
                    ->orWhere('activity_log_metadata', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }
}
