<?php

namespace App\Modules\Report\Exports;

use App\Modules\ActivityLog\Models\ActivityLog;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyDuplicateMatch;
use App\Modules\Report\Enums\ExportType;
use App\Modules\Report\Repositories\ReportRepository;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ExportDataProvider
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headers: list<string>, rows: list<list<scalar|null>>, title: string}
     */
    public function dataset(ExportType $type, array $filters = []): array
    {
        return match ($type) {
            ExportType::Properties => $this->propertiesDataset($filters),
            ExportType::Users => $this->usersDataset($filters),
            ExportType::ActivityLogs => $this->activityLogsDataset($filters),
            ExportType::Duplicates => $this->duplicatesDataset($filters),
            ExportType::DashboardSummary => $this->dashboardSummaryDataset(),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headers: list<string>, rows: list<list<scalar|null>>, title: string}
     */
    private function propertiesDataset(array $filters): array
    {
        $headers = [
            'property_id',
            'property_reference_id',
            'property_record_type',
            'property_status',
            'property_owner_name',
            'property_price',
            'property_district',
            'property_village',
            'property_created_at',
        ];

        $query = Property::query()
            ->select([
                'property_id',
                'property_reference_id',
                'property_record_type',
                'property_status',
                'property_owner_name',
                'property_price',
                'property_district',
                'property_village',
                'created_at',
            ]);

        $this->applyDateFilter($query, $filters, 'created_at');

        $rows = $query->orderByDesc('property_id')->cursor()->map(
            static fn (Property $property): array => [
                $property->property_id,
                $property->property_reference_id,
                $property->property_record_type?->value,
                $property->property_status?->value,
                $property->property_owner_name,
                $property->property_price,
                $property->property_district,
                $property->property_village,
                $property->created_at?->toIso8601String(),
            ],
        )->all();

        return ['headers' => $headers, 'rows' => $rows, 'title' => 'Properties Export'];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headers: list<string>, rows: list<list<scalar|null>>, title: string}
     */
    private function usersDataset(array $filters): array
    {
        $headers = [
            'user_id',
            'user_full_name',
            'user_email',
            'user_phone',
            'user_role',
            'user_is_active',
            'user_created_at',
        ];

        $query = User::query()->select([
            'user_id',
            'user_full_name',
            'user_email',
            'user_phone',
            'user_role',
            'user_is_active',
            'created_at',
        ]);

        $this->applyDateFilter($query, $filters, 'created_at');

        $rows = $query->orderByDesc('user_id')->cursor()->map(
            static fn (User $user): array => [
                $user->user_id,
                $user->user_full_name,
                $user->user_email,
                $user->user_phone,
                $user->user_role?->value,
                $user->user_is_active ? 'yes' : 'no',
                $user->created_at?->toIso8601String(),
            ],
        )->all();

        return ['headers' => $headers, 'rows' => $rows, 'title' => 'Users Export'];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headers: list<string>, rows: list<list<scalar|null>>, title: string}
     */
    private function activityLogsDataset(array $filters): array
    {
        $headers = [
            'activity_log_id',
            'activity_log_user_name',
            'activity_log_user_role',
            'activity_log_type',
            'activity_log_action',
            'activity_log_description',
            'activity_log_entity_type',
            'activity_log_entity_id',
            'activity_log_created_at',
        ];

        $query = ActivityLog::query();

        $this->applyDateFilter($query, $filters, 'activity_log_created_at');

        $rows = $query->orderByDesc('activity_log_id')->cursor()->map(
            static fn (ActivityLog $log): array => [
                $log->activity_log_id,
                $log->activity_log_user_name,
                $log->activity_log_user_role,
                $log->activity_log_type->value,
                $log->activity_log_action,
                $log->activity_log_description,
                $log->activity_log_entity_type,
                $log->activity_log_entity_id,
                $log->activity_log_created_at?->toIso8601String(),
            ],
        )->all();

        return ['headers' => $headers, 'rows' => $rows, 'title' => 'Activity Logs Export'];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<scalar|null>>, title: string}
     */
    private function duplicatesDataset(array $filters): array
    {
        $headers = [
            'property_duplicate_match_id',
            'property_id',
            'matched_property_id',
            'property_duplicate_match_percentage',
            'property_duplicate_match_status',
            'created_at',
        ];

        if (! Schema::hasTable('property_duplicate_matches')) {
            return ['headers' => $headers, 'rows' => [], 'title' => 'Duplicates Export'];
        }

        $query = PropertyDuplicateMatch::query();
        $this->applyDateFilter($query, $filters, 'created_at');

        $rows = $query->orderByDesc('property_duplicate_match_id')->cursor()->map(
            static fn (PropertyDuplicateMatch $match): array => [
                $match->property_duplicate_match_id,
                $match->property_id,
                $match->matched_property_id,
                $match->property_duplicate_match_percentage,
                $match->property_duplicate_match_status?->value,
                $match->created_at?->toIso8601String(),
            ],
        )->all();

        return ['headers' => $headers, 'rows' => $rows, 'title' => 'Duplicates Export'];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<scalar|null>>, title: string}
     */
    private function dashboardSummaryDataset(): array
    {
        $headers = ['metric', 'value'];
        $summary = $this->reportRepository->summaryMetrics();
        $propertyReports = $this->reportRepository->propertyReports();
        $userReports = $this->reportRepository->userReports();
        $duplicateReports = $this->reportRepository->duplicateReports();

        $rows = [];

        foreach (array_merge($summary, $propertyReports, $userReports, $duplicateReports) as $metric => $value) {
            $rows[] = [$metric, $value];
        }

        return ['headers' => $headers, 'rows' => $rows, 'title' => 'Dashboard Summary Export'];
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyDateFilter(Builder $query, array $filters, string $column): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate($column, '>=', Carbon::parse($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate($column, '<=', Carbon::parse($filters['date_to']));
        }
    }
}
