<?php

namespace App\Modules\Report\Services;

use App\Modules\Report\Enums\ReportPeriod;
use App\Modules\Report\Repositories\ReportRepository;

class ReportService
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(ReportPeriod $period = ReportPeriod::Daily): array
    {
        return [
            'summary' => $this->reportRepository->summaryMetrics(),
            'daily_activity' => $this->reportRepository->dailyActivityReport($period),
            'user_reports' => $this->reportRepository->userReports(),
            'property_reports' => $this->reportRepository->propertyReports(),
            'duplicate_reports' => $this->reportRepository->duplicateReports(),
        ];
    }
}
