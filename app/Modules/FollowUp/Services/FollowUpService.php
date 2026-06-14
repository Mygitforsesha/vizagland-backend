<?php

namespace App\Modules\FollowUp\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\FollowUp\Enums\FollowUpStatus;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\FollowUp\Repositories\FollowUpRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class FollowUpService
{
    public function __construct(
        private readonly FollowUpRepository $followUpRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user): FollowUp
    {
        $followUp = DB::transaction(function () use ($data, $user) {
            return $this->followUpRepository->create([
                ...$data,
                'follow_up_status' => FollowUpStatus::Pending,
                'follow_up_created_by' => $user->user_id,
            ]);
        });

        $this->activityLogService->log(
            type: ActivityLogType::FollowUp,
            action: 'created',
            description: "Follow-up created (ID: {$followUp->follow_up_id})",
            entityType: 'follow_up',
            entityId: $followUp->follow_up_id,
            user: $user,
        );

        return $followUp;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $followUpId, array $attributes, User $user): FollowUp
    {
        $followUp = DB::transaction(function () use ($followUpId, $attributes, $user) {
            $followUp = $this->followUpRepository->findById($followUpId);

            if ($followUp === null || ! $this->followUpRepository->userCanAccess($followUp, $user)) {
                throw (new ModelNotFoundException)->setModel(FollowUp::class, [$followUpId]);
            }

            if (isset($attributes['follow_up_status'])) {
                $status = $attributes['follow_up_status'] instanceof FollowUpStatus
                    ? $attributes['follow_up_status']
                    : FollowUpStatus::from($attributes['follow_up_status']);

                $attributes['follow_up_status'] = $status;

                if ($status === FollowUpStatus::Completed && ! isset($attributes['follow_up_completed_at'])) {
                    $attributes['follow_up_completed_at'] = now();
                }
            }

            if ($attributes === []) {
                return $followUp;
            }

            return $this->followUpRepository->update($followUp, $attributes);
        });

        $this->activityLogService->log(
            type: ActivityLogType::FollowUp,
            action: 'updated',
            description: "Follow-up updated (ID: {$followUp->follow_up_id})",
            entityType: 'follow_up',
            entityId: $followUp->follow_up_id,
            user: $user,
        );

        return $followUp;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage, User $user): LengthAwarePaginator
    {
        $scopedUser = ($user->isAdmin() || $user->isSuperAdmin()) ? null : $user;

        return $this->followUpRepository->paginate($filters, $perPage, $scopedUser);
    }

    public function show(int $followUpId, User $user): FollowUp
    {
        $followUp = $this->followUpRepository->findById($followUpId);

        if ($followUp === null || ! $this->followUpRepository->userCanAccess($followUp, $user)) {
            throw (new ModelNotFoundException)->setModel(FollowUp::class, [$followUpId]);
        }

        return $followUp;
    }

    /**
     * @return Collection<int, FollowUp>
     */
    public function listByProperty(int $propertyId): Collection
    {
        return $this->followUpRepository->getByPropertyId($propertyId);
    }

    /**
     * @return Collection<int, FollowUp>
     */
    public function listByLead(int $leadId): Collection
    {
        return $this->followUpRepository->getByLeadId($leadId);
    }
}
