<?php

namespace App\Modules\Lead\Repositories;

use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Models\LeadAssignment;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class LeadRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage, ?User $scopedUser = null): LengthAwarePaginator
    {
        $query = Lead::query()
            ->with(['property', 'createdBy', 'assignedTo'])
            ->orderByDesc('created_at');

        if ($scopedUser !== null) {
            $this->applyUserScope($query, $scopedUser);
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function findById(int $leadId): ?Lead
    {
        return Lead::query()
            ->with(['property', 'createdBy', 'assignedTo', 'assignments.assignedTo', 'assignments.assignedBy'])
            ->where('lead_id', $leadId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Lead
    {
        return Lead::query()->create($attributes);
    }

    public function update(Lead $lead, array $attributes): Lead
    {
        $lead->update($attributes);

        return $lead->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createAssignment(array $attributes): LeadAssignment
    {
        return LeadAssignment::query()->create($attributes);
    }

    /**
     * @param  Builder<Lead>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['lead_status'])) {
            $query->where('lead_status', $filters['lead_status']);
        }

        if (! empty($filters['lead_source'])) {
            $query->where('lead_source', $filters['lead_source']);
        }

        if (! empty($filters['lead_assigned_to'])) {
            $query->where('lead_assigned_to', $filters['lead_assigned_to']);
        }
    }

    /**
     * @param  Builder<Lead>  $query
     */
    private function applyUserScope(Builder $query, User $user): void
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return;
        }

        if ($user->isEmployee()) {
            $query->where('lead_assigned_to', $user->id);

            return;
        }

        if ($user->isAgent()) {
            $query->where('lead_created_by', $user->id);
        }
    }
}
