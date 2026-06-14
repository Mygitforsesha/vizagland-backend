<?php

namespace App\Modules\Lead\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Lead\Enums\LeadSource;
use App\Modules\Lead\Enums\LeadStatus;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Repositories\LeadRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPublic(array $data): Lead
    {
        $lead = $this->leadRepository->create([
            ...$data,
            'lead_source' => LeadSource::Public,
            'lead_status' => LeadStatus::Open,
            'lead_created_by' => null,
            'lead_assigned_to' => null,
        ]);

        $this->activityLogService->log(
            type: ActivityLogType::Lead,
            action: 'created',
            description: "Lead created: {$lead->lead_name}",
            entityType: 'lead',
            entityId: $lead->lead_id,
        );

        return $lead;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createAuthenticated(array $data, User $user, LeadSource $source): Lead
    {
        $lead = $this->leadRepository->create([
            ...$data,
            'lead_source' => $source,
            'lead_status' => LeadStatus::Open,
            'lead_created_by' => $user->user_id,
            'lead_assigned_to' => null,
        ]);

        $this->activityLogService->log(
            type: ActivityLogType::Lead,
            action: 'created',
            description: "Lead created: {$lead->lead_name}",
            entityType: 'lead',
            entityId: $lead->lead_id,
            user: $user,
        );

        return $lead;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage, User $user): LengthAwarePaginator
    {
        $scopedUser = ($user->isAdmin() || $user->isSuperAdmin()) ? null : $user;

        return $this->leadRepository->paginate($filters, $perPage, $scopedUser);
    }

    public function show(int $leadId, User $user): Lead
    {
        $lead = $this->leadRepository->findById($leadId);

        if ($lead === null || ! $this->leadRepository->userCanAccess($lead, $user)) {
            throw (new ModelNotFoundException)->setModel(Lead::class, [$leadId]);
        }

        return $lead;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $leadId, array $attributes, User $user): Lead
    {
        $lead = DB::transaction(function () use ($leadId, $attributes, $user) {
            $lead = $this->leadRepository->findById($leadId);

            if ($lead === null || ! $this->leadRepository->userCanAccess($lead, $user)) {
                throw (new ModelNotFoundException)->setModel(Lead::class, [$leadId]);
            }

            if ($attributes === []) {
                return $lead;
            }

            return $this->leadRepository->update($lead, $attributes);
        });

        $this->activityLogService->log(
            type: ActivityLogType::Lead,
            action: 'updated',
            description: "Lead updated: {$lead->lead_name}",
            entityType: 'lead',
            entityId: $lead->lead_id,
            user: $user,
        );

        return $lead;
    }

    public function assign(int $leadId, int $assigneeId, User $assignedBy, ?string $remarks): Lead
    {
        $lead = DB::transaction(function () use ($leadId, $assigneeId, $assignedBy, $remarks) {
            $lead = $this->leadRepository->findById($leadId);

            if ($lead === null) {
                throw (new ModelNotFoundException)->setModel(Lead::class, [$leadId]);
            }

            $this->leadRepository->createAssignment([
                'lead_id' => $leadId,
                'lead_assigned_to' => $assigneeId,
                'lead_assigned_by' => $assignedBy->user_id,
                'lead_assignment_remarks' => $remarks,
            ]);

            return $this->leadRepository->update($lead, [
                'lead_assigned_to' => $assigneeId,
                'lead_status' => LeadStatus::InProgress,
            ]);
        });

        $this->activityLogService->log(
            type: ActivityLogType::Lead,
            action: 'assigned',
            description: "Lead assigned: {$lead->lead_name}",
            entityType: 'lead',
            entityId: $lead->lead_id,
            user: $assignedBy,
            metadata: ['lead_assigned_to' => $assigneeId],
        );

        return $lead;
    }
}
