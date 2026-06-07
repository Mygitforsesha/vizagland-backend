<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepository;
use App\Modules\Lead\Enums\LeadStatus;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\User\Enums\UserRole;
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
            'assigned_properties' => $this->dashboardRepository->employeeAssignedPropertiesCount($user->id),
            'completed_properties' => $this->dashboardRepository->employeeAssignedPropertiesCount(
                $user->id,
                PropertyStatus::Approved,
            ),
            'draft_properties' => $this->dashboardRepository->employeeAssignedPropertiesCount(
                $user->id,
                PropertyStatus::Draft,
            ),
            'assigned_leads' => $this->dashboardRepository->employeeLeadsCount($user->id),
            'open_leads' => $this->dashboardRepository->employeeLeadsCount($user->id, LeadStatus::Open),
            'closed_leads' => $this->dashboardRepository->employeeLeadsCount($user->id, LeadStatus::Closed),
            'today_followups' => $this->dashboardRepository->todayFollowUpsCount($user->id),
            'pending_followups' => $this->dashboardRepository->pendingFollowUpsCount($user->id),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function agentDashboard(User $user): array
    {
        return [
            'my_properties' => $this->dashboardRepository->agentPropertiesCount($user->id),
            'draft_properties' => $this->dashboardRepository->agentPropertiesCount($user->id, PropertyStatus::Draft),
            'approved_properties' => $this->dashboardRepository->agentPropertiesCount($user->id, PropertyStatus::Approved),
            'rejected_properties' => $this->dashboardRepository->agentPropertiesCount($user->id, PropertyStatus::Rejected),
            'my_leads' => $this->dashboardRepository->agentLeadsCount($user->id),
            'converted_leads' => $this->dashboardRepository->agentLeadsCount($user->id, LeadStatus::Converted),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function adminDashboard(): array
    {
        return [
            'total_properties' => $this->dashboardRepository->countProperties(),
            'draft_properties' => $this->dashboardRepository->countProperties(
                fn ($query) => $query->where('property_status', PropertyStatus::Draft),
            ),
            'pending_review_properties' => $this->dashboardRepository->countProperties(
                fn ($query) => $query->where('property_status', PropertyStatus::PendingReview),
            ),
            'approved_properties' => $this->dashboardRepository->countProperties(
                fn ($query) => $query->where('property_status', PropertyStatus::Approved),
            ),
            'rejected_properties' => $this->dashboardRepository->countProperties(
                fn ($query) => $query->where('property_status', PropertyStatus::Rejected),
            ),
            'total_agents' => $this->dashboardRepository->countUsersByRole(UserRole::Agent),
            'total_employees' => $this->dashboardRepository->countUsersByRole(UserRole::Employee),
            'total_leads' => $this->dashboardRepository->countLeads(),
            'open_leads' => $this->dashboardRepository->countLeads(
                fn ($query) => $query->where('lead_status', LeadStatus::Open),
            ),
            'closed_leads' => $this->dashboardRepository->countLeads(
                fn ($query) => $query->where('lead_status', LeadStatus::Closed),
            ),
            'today_properties' => $this->dashboardRepository->propertiesCreatedTodayCount(),
            'this_month_properties' => $this->dashboardRepository->propertiesCreatedThisMonthCount(),
        ];
    }
}
