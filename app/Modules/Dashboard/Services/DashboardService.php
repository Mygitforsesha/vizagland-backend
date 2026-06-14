<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepository;
use App\Modules\Lead\Enums\LeadStatus;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\User\Models\User;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository,
    ) {}

    /**
     * @return array<string, int>
     */
    public function employeeDashboard(User $user): array
    {
        return [
            'assigned_properties' => $this->dashboardRepository->employeeAssignedPropertiesCount($user->user_id),
            'completed_properties' => $this->dashboardRepository->employeeAssignedPropertiesCount(
                $user->user_id,
                PropertyStatus::Approved,
            ),
            'draft_properties' => $this->dashboardRepository->employeeAssignedPropertiesCount(
                $user->user_id,
                PropertyStatus::Draft,
            ),
            'assigned_leads' => $this->dashboardRepository->employeeLeadsCount($user->user_id),
            'open_leads' => $this->dashboardRepository->employeeLeadsCount($user->user_id, LeadStatus::Open),
            'closed_leads' => $this->dashboardRepository->employeeLeadsCount($user->user_id, LeadStatus::Closed),
            'today_followups' => $this->dashboardRepository->todayFollowUpsCount($user->user_id),
            'pending_followups' => $this->dashboardRepository->pendingFollowUpsCount($user->user_id),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function agentDashboard(User $user): array
    {
        return [
            'my_properties' => $this->dashboardRepository->agentPropertiesCount($user->user_id),
            'draft_properties' => $this->dashboardRepository->agentPropertiesCount($user->user_id, PropertyStatus::Draft),
            'approved_properties' => $this->dashboardRepository->agentPropertiesCount($user->user_id, PropertyStatus::Approved),
            'rejected_properties' => $this->dashboardRepository->agentPropertiesCount($user->user_id, PropertyStatus::Rejected),
            'my_leads' => $this->dashboardRepository->agentLeadsCount($user->user_id),
            'converted_leads' => $this->dashboardRepository->agentLeadsCount($user->user_id, LeadStatus::Converted),
        ];
    }
}
