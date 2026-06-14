<?php

namespace App\Modules\Report\Repositories;

use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Enums\ReviewStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyDuplicateMatch;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use App\Modules\Report\Enums\ReportPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportRepository
{
    /**
     * @return array<string, int>
     */
    public function summaryMetrics(): array
    {
        $today = Carbon::today();

        return [
            'today_property_submissions' => Property::query()
                ->where('property_record_type', PropertyRecordType::VizaglandCopy)
                ->whereDate('property_submitted_at', $today)
                ->count(),
            'today_verified_properties' => Property::query()
                ->where('property_record_type', PropertyRecordType::VizaglandCopy)
                ->whereDate('property_approved_at', $today)
                ->count(),
            'duplicate_properties_found' => $this->duplicatePropertiesCount(),
            'new_users_today' => User::query()
                ->whereDate('created_at', $today)
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function propertyReports(): array
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
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_draft',
                [PropertyStatus::Draft->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_pending_review',
                [PropertyStatus::PendingReview->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_approved',
                [PropertyStatus::Approved->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN property_status = ? THEN 1 ELSE 0 END) as total_rejected',
                [PropertyStatus::Rejected->value],
            )
            ->first();

        return [
            'total_properties' => (int) ($stats->total_properties ?? 0),
            'total_original_properties' => (int) ($stats->total_original_properties ?? 0),
            'total_vizagland_copy_properties' => (int) ($stats->total_vizagland_copy_properties ?? 0),
            'total_draft' => (int) ($stats->total_draft ?? 0),
            'total_pending_review' => (int) ($stats->total_pending_review ?? 0),
            'total_approved' => (int) ($stats->total_approved ?? 0),
            'total_rejected' => (int) ($stats->total_rejected ?? 0),
            'total_request_changes' => $this->countRequestChangesProperties(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function userReports(): array
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
            ->first();

        return [
            'total_users' => (int) ($stats->total_users ?? 0),
            'total_admin_users' => (int) ($stats->total_admin_users ?? 0),
            'total_employee_users' => (int) ($stats->total_employee_users ?? 0),
            'total_agent_users' => (int) ($stats->total_agent_users ?? 0),
            'total_member_users' => (int) ($stats->total_member_users ?? 0),
        ];
    }

    /**
     * @return list<array<string, int|string>>
     */
    public function dailyActivityReport(ReportPeriod $period): array
    {
        return match ($period) {
            ReportPeriod::Daily => $this->dailyBuckets(30),
            ReportPeriod::Weekly => $this->weeklyBuckets(12),
            ReportPeriod::Monthly => $this->monthlyBuckets(12),
        };
    }

    /**
     * @return array<string, float|int>
     */
    public function duplicateReports(): array
    {
        if (! Schema::hasTable('property_duplicate_matches')) {
            return [
                'duplicate_properties_count' => 0,
                'duplicate_match_percentage' => 0.0,
            ];
        }

        $duplicateCount = $this->duplicatePropertiesCount();
        $averageMatch = PropertyDuplicateMatch::query()->avg('property_duplicate_match_percentage');

        return [
            'duplicate_properties_count' => $duplicateCount,
            'duplicate_match_percentage' => round((float) ($averageMatch ?? 0), 2),
        ];
    }

    private function duplicatePropertiesCount(): int
    {
        if (! Schema::hasTable('property_duplicate_matches')) {
            return 0;
        }

        return (int) PropertyDuplicateMatch::query()->distinct()->count('property_id');
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

    /**
     * @return list<array<string, int|string>>
     */
    private function dailyBuckets(int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $submissions = $this->groupedCount(
            table: 'properties',
            dateColumn: 'property_submitted_at',
            startDate: $startDate,
            extra: static fn ($query) => $query->where('property_record_type', PropertyRecordType::VizaglandCopy->value),
        );
        $verified = $this->groupedCount('properties', 'property_approved_at', $startDate);
        $users = $this->groupedCount('users', 'created_at', $startDate);

        $report = [];

        for ($index = 0; $index < $days; $index++) {
            $date = $startDate->copy()->addDays($index)->toDateString();
            $report[] = [
                'period_label' => $date,
                'property_submissions' => $submissions[$date] ?? 0,
                'verified_properties' => $verified[$date] ?? 0,
                'new_users' => $users[$date] ?? 0,
            ];
        }

        return $report;
    }

    /**
     * @return list<array<string, int|string>>
     */
    private function weeklyBuckets(int $weeks): array
    {
        $startDate = Carbon::now()->startOfWeek()->subWeeks($weeks - 1);
        $report = [];

        for ($index = 0; $index < $weeks; $index++) {
            $weekStart = $startDate->copy()->addWeeks($index);
            $weekEnd = $weekStart->copy()->endOfWeek();
            $label = $weekStart->format('Y-m-d').' to '.$weekEnd->format('Y-m-d');

            $report[] = [
                'period_label' => $label,
                'property_submissions' => $this->countBetween('properties', 'property_submitted_at', $weekStart, $weekEnd, static fn ($query) => $query->where('property_record_type', PropertyRecordType::VizaglandCopy->value)),
                'verified_properties' => $this->countBetween('properties', 'property_approved_at', $weekStart, $weekEnd),
                'new_users' => $this->countBetween('users', 'created_at', $weekStart, $weekEnd),
            ];
        }

        return $report;
    }

    /**
     * @return list<array<string, int|string>>
     */
    private function monthlyBuckets(int $months): array
    {
        $startDate = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $report = [];

        for ($index = 0; $index < $months; $index++) {
            $monthDate = $startDate->copy()->addMonths($index);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();

            $report[] = [
                'period_label' => $monthDate->format('M Y'),
                'property_submissions' => $this->countBetween('properties', 'property_submitted_at', $monthStart, $monthEnd, static fn ($query) => $query->where('property_record_type', PropertyRecordType::VizaglandCopy->value)),
                'verified_properties' => $this->countBetween('properties', 'property_approved_at', $monthStart, $monthEnd),
                'new_users' => $this->countBetween('users', 'created_at', $monthStart, $monthEnd),
            ];
        }

        return $report;
    }

    /**
     * @return array<string, int>
     */
    private function groupedCount(
        string $table,
        string $dateColumn,
        Carbon $startDate,
        ?callable $extra = null,
    ): array {
        $query = DB::table($table)
            ->selectRaw('DATE('.$dateColumn.') as bucket_date')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull($dateColumn)
            ->whereDate($dateColumn, '>=', $startDate)
            ->groupByRaw('DATE('.$dateColumn.')');

        if ($extra !== null) {
            $extra($query);
        }

        return $query->pluck('total', 'bucket_date')
            ->map(static fn ($value): int => (int) $value)
            ->all();
    }

    private function countBetween(
        string $table,
        string $dateColumn,
        Carbon $start,
        Carbon $end,
        ?callable $extra = null,
    ): int {
        $query = DB::table($table)
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$start, $end]);

        if ($extra !== null) {
            $extra($query);
        }

        return (int) $query->count();
    }
}
