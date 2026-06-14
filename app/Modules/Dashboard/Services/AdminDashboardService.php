<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepository;

class AdminDashboardService
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $propertyStatistics = $this->dashboardRepository->propertyStatistics();
        $userStatistics = $this->dashboardRepository->userStatistics();
        $verificationStatistics = $this->dashboardRepository->verificationStatistics();

        return [
            'dashboard_summary' => $this->buildDashboardSummary(
                $propertyStatistics,
                $userStatistics,
                $verificationStatistics,
            ),
            'property_statistics' => $propertyStatistics,
            'user_statistics' => $userStatistics,
            'verification_statistics' => $verificationStatistics,
            'property_posting_trend' => $this->dashboardRepository->propertyPostingTrend(),
            'user_activity' => $this->dashboardRepository->userActivityLastSevenDays(),
            'recent_activity' => $this->dashboardRepository->recentPropertyActivity(),
            'duplicate_statistics' => $this->dashboardRepository->duplicateStatistics(),
        ];
    }

    /**
     * @param  array<string, int|float>  $propertyStatistics
     * @param  array<string, int>  $userStatistics
     * @param  array<string, float>  $verificationStatistics
     * @return array<string, int|float>
     */
    private function buildDashboardSummary(
        array $propertyStatistics,
        array $userStatistics,
        array $verificationStatistics,
    ): array {
        return [
            'total_properties' => $propertyStatistics['total_properties'],
            'total_users' => $userStatistics['total_users'],
            'total_pending_review_properties' => $propertyStatistics['total_pending_review_properties'],
            'total_active_users' => $userStatistics['total_active_users'],
            'approved_percentage' => $verificationStatistics['approved_percentage'],
            'pending_percentage' => $verificationStatistics['pending_percentage'],
        ];
    }
}
