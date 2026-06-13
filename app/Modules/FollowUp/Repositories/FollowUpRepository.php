<?php

namespace App\Modules\FollowUp\Repositories;

use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FollowUpRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage, ?User $scopedUser = null): LengthAwarePaginator
    {
        $query = FollowUp::query()
            ->with(['property', 'lead', 'createdBy', 'assignedTo'])
            ->orderBy('follow_up_scheduled_at');

        if ($scopedUser !== null) {
            $this->applyUserScope($query, $scopedUser);
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, FollowUp>
     */
    public function getByPropertyId(int $propertyId): Collection
    {
        return FollowUp::query()
            ->with(['createdBy', 'assignedTo'])
            ->where('follow_up_property_id', $propertyId)
            ->orderByDesc('follow_up_scheduled_at')
            ->get();
    }

    /**
     * @return Collection<int, FollowUp>
     */
    public function getByLeadId(int $leadId): Collection
    {
        return FollowUp::query()
            ->with(['createdBy', 'assignedTo'])
            ->where('follow_up_lead_id', $leadId)
            ->orderByDesc('follow_up_scheduled_at')
            ->get();
    }

    public function findById(int $followUpId): ?FollowUp
    {
        return FollowUp::query()
            ->with(['property', 'lead', 'createdBy', 'assignedTo'])
            ->where('follow_up_id', $followUpId)
            ->first();
    }

    public function userCanAccess(FollowUp $followUp, User $user): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        if ($user->isEmployee()) {
            return $followUp->follow_up_assigned_to === $user->user_id;
        }

        if ($user->isAgent()) {
            return $followUp->follow_up_created_by === $user->user_id;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): FollowUp
    {
        return FollowUp::query()->create($attributes);
    }

    public function update(FollowUp $followUp, array $attributes): FollowUp
    {
        $followUp->update($attributes);

        return $followUp->fresh();
    }

    /**
     * @param  Builder<FollowUp>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['follow_up_status'])) {
            $query->where('follow_up_status', $filters['follow_up_status']);
        }

        if (! empty($filters['follow_up_type'])) {
            $query->where('follow_up_type', $filters['follow_up_type']);
        }

        if (! empty($filters['follow_up_assigned_to'])) {
            $query->where('follow_up_assigned_to', $filters['follow_up_assigned_to']);
        }

        if (! empty($filters['follow_up_property_id'])) {
            $query->where('follow_up_property_id', $filters['follow_up_property_id']);
        }

        if (! empty($filters['follow_up_lead_id'])) {
            $query->where('follow_up_lead_id', $filters['follow_up_lead_id']);
        }
    }

    /**
     * @param  Builder<FollowUp>  $query
     */
    private function applyUserScope(Builder $query, User $user): void
    {
        if ($user->isEmployee()) {
            $query->where('follow_up_assigned_to', $user->user_id);

            return;
        }

        if ($user->isAgent()) {
            $query->where('follow_up_created_by', $user->user_id);
        }
    }
}
