<?php

namespace App\Modules\Dashboard\Repositories;

use App\Modules\FollowUp\Enums\FollowUpStatus;
use App\Modules\Lead\Enums\LeadStatus;
use App\Modules\Lead\Models\Lead;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use App\Modules\FollowUp\Models\FollowUp;
use Illuminate\Support\Carbon;

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
            ->where('role', $role)
            ->where('is_active', true)
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
            ->where('property_assigned_to', $userId)
            ->when($status, fn ($query) => $query->where('property_status', $status))
            ->count();
    }

    public function agentPropertiesCount(int $userId, ?PropertyStatus $status = null): int
    {
        return Property::query()
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
}
