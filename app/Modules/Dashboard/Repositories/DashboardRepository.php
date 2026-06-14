<?php

namespace App\Modules\Dashboard\Repositories;

use App\Modules\FollowUp\Enums\FollowUpStatus;
use App\Modules\Lead\Enums\LeadStatus;
use App\Modules\Lead\Models\Lead;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertyReviewAction;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Enums\ReviewStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyDuplicateMatch;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use App\Modules\FollowUp\Models\FollowUp;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardRepository
{
    public function countProperties(?callable $constraint = null): int
    {
        $query = Property::query();

        if ($constraint !== null) {
            $constraint($query);
        }

        return $query->count();
    }

    public function countLeads(?callable $constraint = null): int
    {
        $query = Lead::query();

        if ($constraint !== null) {
            $constraint($query);
        }

        return $query->count();
    }

    public function countUsersByRole(UserRole $role): int
    {
        return User::query()
            ->where('user_role', $role)
            ->where('user_is_active', true)
            ->count();
    }

    public function countFollowUps(int $userId, callable $constraint): int
    {
        $query = FollowUp::query()
            ->where('follow_up_assigned_to', $userId);

        $constraint($query);

        return $query->count();
    }

    public function employeeAssignedPropertiesCount(int $userId, ?PropertyStatus $status = null): int
    {
        return Property::query()
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_assigned_user_id', $userId)
            ->when($status, fn ($query) => $query->where('property_status', $status))
            ->count();
    }

    public function agentPropertiesCount(int $userId, ?PropertyStatus $status = null): int
    {
        return Property::query()
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_created_by', $userId)
            ->when($status, fn ($query) => $query->where('property_status', $status))
            ->count();
    }

    public function employeeLeadsCount(int $userId, ?LeadStatus $status = null): int
    {
        return Lead::query()
            ->where('lead_assigned_to', $userId)
            ->when($status, fn ($query) => $query->where('lead_status', $status))
            ->count();
    }

    public function agentLeadsCount(int $userId, ?LeadStatus $status = null): int
    {
        return Lead::query()
            ->where('lead_created_by', $userId)
            ->when($status, fn ($query) => $query->where('lead_status', $status))
            ->count();
    }

    public function todayFollowUpsCount(int $userId): int
    {
        return FollowUp::query()
            ->where('follow_up_assigned_to', $userId)
            ->where('follow_up_status', FollowUpStatus::Pending)
            ->whereDate('follow_up_scheduled_at', Carbon::today())
            ->count();
    }

    public function pendingFollowUpsCount(int $userId): int
    {
        return FollowUp::query()
            ->where('follow_up_assigned_to', $userId)
            ->where('follow_up_status', FollowUpStatus::Pending)
            ->count();
    }

    public function propertiesCreatedTodayCount(): int
    {
        return Property::query()
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    public function propertiesCreatedThisMonthCount(): int
    {
        return Property::query()
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
    }

    /**
     * @return array<string, int>
     */
    public function propertyStatistics(): array
    {
        $stats = Property::query()
            ->selectRaw('COUNT(*) as total_properties')
            ->selectRaw(
                'SUM(CASE WHEN property_record_type = ? THEN 1 ELSE 0 END) as total_original_properties',
                [PropertyRecordType::Original->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_record_type = ? THEN 1 ELSE 0 END) as total_vizagland_copy_properties',
                [PropertyRecordType::VizaglandCopy->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_pending_review_properties',
                [PropertyStatus::PendingReview->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_approved_properties',
                [PropertyStatus::Approved->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_rejected_properties',
                [PropertyStatus::Rejected->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_draft_properties',
                [PropertyStatus::Draft->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_is_featured = 1 THEN 1 ELSE 0 END) as total_featured_properties',
            )
            ->selectRaw(
                'SUM(CASE WHEN property_is_deleted = 1 THEN 1 ELSE 0 END) as total_deleted_properties',
            )
            ->first();

        return [
            'total_properties' => (int) ($stats->total_properties ?? 0),
            'total_original_properties' => (int) ($stats->total_original_properties ?? 0),
            'total_vizagland_copy_properties' => (int) ($stats->total_vizagland_copy_properties ?? 0),
            'total_pending_review_properties' => (int) ($stats->total_pending_review_properties ?? 0),
            'total_approved_properties' => (int) ($stats->total_approved_properties ?? 0),
            'total_rejected_properties' => (int) ($stats->total_rejected_properties ?? 0),
            'total_request_changes_properties' => $this->countRequestChangesProperties(),
            'total_draft_properties' => (int) ($stats->total_draft_properties ?? 0),
            'total_featured_properties' => (int) ($stats->total_featured_properties ?? 0),
            'total_deleted_properties' => (int) ($stats->total_deleted_properties ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function userStatistics(): array
    {
        $stats = User::query()
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw(
                'SUM(CASE WHEN user_role IN (?, ?) THEN 1 ELSE 0 END) as total_admin_users',
                [UserRole::Admin->value, UserRole::SuperAdmin->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_role = ? THEN 1 ELSE 0 END) as total_employee_users',
                [UserRole::Employee->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_role = ? THEN 1 ELSE 0 END) as total_agent_users',
                [UserRole::Agent->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_role = ? THEN 1 ELSE 0 END) as total_member_users',
                [UserRole::Member->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_is_active = 1 THEN 1 ELSE 0 END) as total_active_users',
            )
            ->selectRaw(
                'SUM(CASE WHEN user_is_active = 0 THEN 1 ELSE 0 END) as total_inactive_users',
            )
            ->first();

        return [
            'total_users' => (int) ($stats->total_users ?? 0),
            'total_admin_users' => (int) ($stats->total_admin_users ?? 0),
            'total_employee_users' => (int) ($stats->total_employee_users ?? 0),
            'total_agent_users' => (int) ($stats->total_agent_users ?? 0),
            'total_member_users' => (int) ($stats->total_member_users ?? 0),
            'total_active_users' => (int) ($stats->total_active_users ?? 0),
            'total_inactive_users' => (int) ($stats->total_inactive_users ?? 0),
        ];
    }

    /**
     * @return array<string, float>
     */
    public function verificationStatistics(): array
    {
        $baseQuery = Property::query()
            ->where('property_record_type', PropertyRecordType::VizaglandCopy)
            ->where('property_is_deleted', false);

        $total = (clone $baseQuery)->count();

        if ($total === 0) {
            return [
                'approved_percentage' => 0.0,
                'pending_percentage' => 0.0,
                'rejected_percentage' => 0.0,
                'request_changes_percentage' => 0.0,
            ];
        }

        $approvedCount = (clone $baseQuery)
            ->where('property_status', PropertyStatus::Approved)
            ->count();
        $pendingCount = (clone $baseQuery)
            ->where('property_status', PropertyStatus::PendingReview)
            ->count();
        $rejectedCount = (clone $baseQuery)
            ->where('property_status', PropertyStatus::Rejected)
            ->count();
        $requestChangesCount = $this->countRequestChangesProperties();

        return [
            'approved_percentage' => $this->percentage($approvedCount, $total),
            'pending_percentage' => $this->percentage($pendingCount, $total),
            'rejected_percentage' => $this->percentage($rejectedCount, $total),
            'request_changes_percentage' => $this->percentage($requestChangesCount, $total),
        ];
    }

    /**
     * @return list<array{month: string, property_count: int}>
     */
    public function propertyPostingTrend(): array
    {
        $startDate = Carbon::now()->startOfMonth()->subMonths(11);
        $monthlyCounts = Property::query()
            ->selectRaw('YEAR(created_at) as year')
            ->selectRaw('MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as property_count')
            ->where('created_at', '>=', $startDate)
            ->where('property_is_deleted', false)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn ($row): string => sprintf('%04d-%02d', $row->year, $row->month));

        $trend = [];

        for ($index = 0; $index < 12; $index++) {
            $monthDate = $startDate->copy()->addMonths($index);
            $key = $monthDate->format('Y-m');
            $count = $monthlyCounts->has($key)
                ? (int) $monthlyCounts->get($key)->property_count
                : 0;

            $trend[] = [
                'month' => $monthDate->format('M'),
                'property_count' => $count,
            ];
        }

        return $trend;
    }

    /**
     * @return list<array{
     *     date: string,
     *     admin_count: int,
     *     employee_count: int,
     *     agent_count: int,
     *     member_count: int
     * }>
     */
    public function userActivityLastSevenDays(): array
    {
        if (! Schema::hasColumn('users', 'user_last_login_at')) {
            return [];
        }

        $startDate = Carbon::today()->subDays(6)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $loginCounts = User::query()
            ->selectRaw('DATE(user_last_login_at) as activity_date')
            ->selectRaw(
                'SUM(CASE WHEN user_role IN (?, ?) THEN 1 ELSE 0 END) as admin_count',
                [UserRole::Admin->value, UserRole::SuperAdmin->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_role = ? THEN 1 ELSE 0 END) as employee_count',
                [UserRole::Employee->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_role = ? THEN 1 ELSE 0 END) as agent_count',
                [UserRole::Agent->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN user_role = ? THEN 1 ELSE 0 END) as member_count',
                [UserRole::Member->value],
            )
            ->whereNotNull('user_last_login_at')
            ->whereBetween('user_last_login_at', [$startDate, $endDate])
            ->groupByRaw('DATE(user_last_login_at)')
            ->get()
            ->keyBy('activity_date');

        $activity = [];

        for ($index = 0; $index < 7; $index++) {
            $date = $startDate->copy()->addDays($index)->toDateString();
            $row = $loginCounts->get($date);

            $activity[] = [
                'date' => $date,
                'admin_count' => (int) ($row->admin_count ?? 0),
                'employee_count' => (int) ($row->employee_count ?? 0),
                'agent_count' => (int) ($row->agent_count ?? 0),
                'member_count' => (int) ($row->member_count ?? 0),
            ];
        }

        $hasAnyActivity = collect($activity)->contains(
            fn (array $day): bool => ($day['admin_count'] + $day['employee_count'] + $day['agent_count'] + $day['member_count']) > 0,
        );

        return $hasAnyActivity ? $activity : [];
    }

    /**
     * @return list<array{
     *     property_reference_id: ?string,
     *     property_record_type: ?string,
     *     property_status: ?string,
     *     property_owner_name: ?string,
     *     action_type: string,
     *     action_by_user: ?array{user_id: int, user_full_name: string},
     *     action_at: string
     * }>
     */
    public function recentPropertyActivity(): array
    {
        $events = $this->recentPropertyActivityQuery()
            ->limit(20)
            ->get();

        if ($events->isEmpty()) {
            return [];
        }

        $propertyIds = $events->pluck('property_id')->unique()->values()->all();
        $userIds = $events->pluck('action_by_user_id')->filter()->unique()->values()->all();

        $properties = Property::query()
            ->select([
                'property_id',
                'property_reference_id',
                'property_record_type',
                'property_status',
                'property_owner_name',
            ])
            ->whereIn('property_id', $propertyIds)
            ->get()
            ->keyBy('property_id');

        $users = User::query()
            ->select(['user_id', 'user_full_name'])
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        return $events->map(function ($event) use ($properties, $users): array {
            $property = $properties->get($event->property_id);
            $user = $event->action_by_user_id ? $users->get($event->action_by_user_id) : null;

            return [
                'property_reference_id' => $property?->property_reference_id,
                'property_record_type' => $property?->property_record_type?->value,
                'property_status' => $property?->property_status?->value,
                'property_owner_name' => $property?->property_owner_name,
                'action_type' => $event->action_type,
                'action_by_user' => $user === null ? null : [
                    'user_id' => $user->user_id,
                    'user_full_name' => $user->user_full_name,
                ],
                'action_at' => Carbon::parse($event->action_at)->toIso8601String(),
            ];
        })->all();
    }

    /**
     * @return array{duplicate_properties: int}
     */
    public function duplicateStatistics(): array
    {
        if (! Schema::hasTable('property_duplicate_matches')) {
            return ['duplicate_properties' => 0];
        }

        $duplicateProperties = PropertyDuplicateMatch::query()
            ->distinct()
            ->count('property_id');

        return [
            'duplicate_properties' => $duplicateProperties,
        ];
    }

    private function countRequestChangesProperties(): int
    {
        if (! Schema::hasTable('property_reviews')) {
            return 0;
        }

        return (int) DB::table('property_reviews')
            ->where('property_review_status', ReviewStatus::NeedsRevision->value)
            ->distinct()
            ->count('property_id');
    }

    private function percentage(int $count, int $total): float
    {
        return round(($count / $total) * 100, 2);
    }

    private function recentPropertyActivityQuery(): Builder
    {
        $createdEvents = DB::table('properties')
            ->select([
                'property_id',
                DB::raw("'Created' as action_type"),
                'created_at as action_at',
                'property_created_by as action_by_user_id',
            ])
            ->whereNotNull('created_at');

        $submittedEvents = DB::table('properties')
            ->select([
                'property_id',
                DB::raw("'Submitted' as action_type"),
                'property_submitted_at as action_at',
                'property_created_by as action_by_user_id',
            ])
            ->whereNotNull('property_submitted_at');

        $updatedEvents = DB::table('properties')
            ->select([
                'property_id',
                DB::raw("'Updated' as action_type"),
                'updated_at as action_at',
                'property_created_by as action_by_user_id',
            ])
            ->whereColumn('updated_at', '>', 'created_at');

        $reviewEvents = DB::table('property_reviews')
            ->select([
                'property_id',
                DB::raw("CASE property_review_status
                    WHEN '".ReviewStatus::Approved->value."' THEN 'Approved'
                    WHEN '".ReviewStatus::Rejected->value."' THEN 'Rejected'
                    WHEN '".ReviewStatus::NeedsRevision->value."' THEN 'Request Changes'
                    ELSE 'Updated'
                END as action_type"),
                'property_review_reviewed_at as action_at',
                'property_review_reviewed_by as action_by_user_id',
            ])
            ->whereNotNull('property_review_reviewed_at');

        $queries = collect([$createdEvents, $submittedEvents, $updatedEvents, $reviewEvents]);

        if (Schema::hasTable('property_review_logs')) {
            $logEvents = DB::table('property_review_logs')
                ->select([
                    'property_id',
                    DB::raw("CASE property_review_action
                        WHEN '".PropertyReviewAction::Approved->value."' THEN 'Approved'
                        WHEN '".PropertyReviewAction::Rejected->value."' THEN 'Rejected'
                        ELSE 'Updated'
                    END as action_type"),
                    'property_review_created_at as action_at',
                    'property_review_performed_by_user_id as action_by_user_id',
                ]);

            $queries->push($logEvents);
        }

        /** @var Builder $unionQuery */
        $unionQuery = $queries->shift();

        foreach ($queries as $query) {
            $unionQuery->unionAll($query);
        }

        return DB::query()
            ->fromSub($unionQuery, 'property_activity_events')
            ->orderByDesc('action_at');
    }
}
