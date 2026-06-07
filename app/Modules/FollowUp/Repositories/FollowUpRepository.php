<?php

namespace App\Modules\FollowUp\Repositories;

use App\Modules\FollowUp\Models\FollowUp;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FollowUpRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = FollowUp::query()
            ->with(['property', 'lead', 'createdBy', 'assignedTo'])
            ->orderBy('follow_up_scheduled_at');

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
            ->where('follow_up_id', $followUpId)
            ->first();
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
}
