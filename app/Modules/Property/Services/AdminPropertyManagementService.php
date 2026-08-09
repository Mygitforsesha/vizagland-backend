<?php

namespace App\Modules\Property\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Property\Enums\PropertyReviewAction;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Repositories\PropertyRepository;
use App\Modules\Property\Repositories\PropertyReviewLogRepository;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminPropertyManagementService
{
    public function __construct(
        private readonly PropertyRepository $propertyRepository,
        private readonly PropertyReviewLogRepository $propertyReviewLogRepository,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function approve(int $propertyId, User $admin, ?string $reviewRemarks): Property
    {
        $property = $this->performAction(
            propertyId: $propertyId,
            admin: $admin,
            action: PropertyReviewAction::Approved,
            allowedStatuses: PropertyStatus::cases(),
            propertyUpdates: function () use ($admin, $reviewRemarks): array {
                $now = now();

                return [
                    'property_status' => PropertyStatus::Approved,
                    'property_reviewed_by' => $admin->user_id,
                    'property_review_remarks' => $reviewRemarks,
                    'property_approved_at' => $now,
                    'property_approved_by_user_id' => $admin->user_id,
                    'property_verified' => true,
                    'property_published_at' => $now,
                ];
            },
            logNotes: $reviewRemarks,
        );

        $this->notificationService->notifyPropertyApproved($property);
        $this->logPropertyReview($property, 'approved', 'Approved', $admin);

        return $property;
    }

    public function reject(int $propertyId, User $admin, string $rejectedReason): Property
    {
        $property = $this->performAction(
            propertyId: $propertyId,
            admin: $admin,
            action: PropertyReviewAction::Rejected,
            allowedStatuses: [PropertyStatus::PendingReview],
            propertyUpdates: function () use ($admin, $rejectedReason): array {
                $now = now();

                return [
                    'property_status' => PropertyStatus::Rejected,
                    'property_reviewed_by' => $admin->user_id,
                    'property_rejected_reason' => $rejectedReason,
                    'property_rejected_at' => $now,
                    'property_rejected_by_user_id' => $admin->user_id,
                ];
            },
            logNotes: $rejectedReason,
        );

        $this->notificationService->notifyPropertyRejected($property, $rejectedReason);
        $this->logPropertyReview($property, 'rejected', 'Rejected', $admin);

        return $property;
    }

    public function archive(int $propertyId, User $admin, ?string $archivedReason): Property
    {
        $property = $this->performAction(
            propertyId: $propertyId,
            admin: $admin,
            action: PropertyReviewAction::Archived,
            allowedStatuses: [PropertyStatus::PendingReview, PropertyStatus::Approved],
            propertyUpdates: function () use ($admin, $archivedReason): array {
                $now = now();

                return [
                    'property_status' => PropertyStatus::Archived,
                    'property_reviewed_by' => $admin->user_id,
                    'property_archived_reason' => $archivedReason,
                    'property_archived_at' => $now,
                    'property_archived_by_user_id' => $admin->user_id,
                ];
            },
            logNotes: $archivedReason,
        );

        $this->logPropertyReview($property, 'archived', 'Archived', $admin);

        return $property;
    }

    public function restore(int $propertyId, User $admin): Property
    {
        return $this->performAction(
            propertyId: $propertyId,
            admin: $admin,
            action: PropertyReviewAction::Restored,
            allowedStatuses: [PropertyStatus::Archived, PropertyStatus::Rejected],
            propertyUpdates: function () use ($admin): array {
                return [
                    'property_status' => PropertyStatus::PendingReview,
                    'property_reviewed_by' => $admin->user_id,
                    'property_restored_at' => now(),
                    'property_restored_by_user_id' => $admin->user_id,
                ];
            },
            logNotes: null,
        );
    }

    public function resolve(int $propertyId, User $admin, ?string $resolutionRemarks): Property
    {
        return DB::transaction(function () use ($propertyId, $admin, $resolutionRemarks) {
            $property = $this->propertyRepository->findById($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($property->isOriginal()) {
                throw new RuntimeException('Original properties cannot be resolved.');
            }

            $this->ensureResolvableStatus($property);

            $now = now();

            $updatedProperty = $this->propertyRepository->update($property, [
                'property_status' => PropertyStatus::Resolved,
                'property_resolution_remarks' => $resolutionRemarks,
                'property_resolved_at' => $now,
                'property_resolved_by_user_id' => $admin->user_id,
            ]);

            $referenceId = $updatedProperty->property_reference_id ?? (string) $updatedProperty->property_id;

            $this->activityLogService->log(
                type: ActivityLogType::Property,
                action: 'resolved',
                description: "Property {$referenceId} marked as resolved",
                entityType: 'property',
                entityId: $updatedProperty->property_id,
                user: $admin,
                metadata: ['property_reference_id' => $referenceId],
            );

            $this->notificationService->notifyPropertyResolved($updatedProperty);

            return $updatedProperty;
        });
    }

    private function ensureResolvableStatus(Property $property): void
    {
        $allowedStatuses = [
            PropertyStatus::PendingReview,
            PropertyStatus::RequestChanges,
            PropertyStatus::Approved,
            PropertyStatus::Rejected,
        ];

        if (in_array($property->property_status, $allowedStatuses, true)) {
            return;
        }

        $allowedLabels = implode(', ', array_map(
            static fn (PropertyStatus $status): string => $status->label(),
            $allowedStatuses,
        ));

        throw new RuntimeException(
            "Property cannot be resolved while status is {$property->property_status->label()}. Allowed statuses: {$allowedLabels}.",
        );
    }

    /**
     * @param  list<PropertyStatus>  $allowedStatuses
     * @param  callable(): array<string, mixed>  $propertyUpdates
     */
    private function performAction(
        int $propertyId,
        User $admin,
        PropertyReviewAction $action,
        array $allowedStatuses,
        callable $propertyUpdates,
        ?string $logNotes,
    ): Property {
        return DB::transaction(function () use ($propertyId, $admin, $action, $allowedStatuses, $propertyUpdates, $logNotes) {
            $property = $this->propertyRepository->findById($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            $this->ensureVizaglandCopy($property);
            $this->ensureAllowedStatus($property, $allowedStatuses, $action);

            $updatedProperty = $this->propertyRepository->update($property, $propertyUpdates());

            $this->propertyReviewLogRepository->create([
                'property_id' => $propertyId,
                'property_review_action' => $action,
                'property_review_notes' => $logNotes,
                'property_review_performed_by_user_id' => $admin->user_id,
            ]);

            return $updatedProperty;
        });
    }

    private function ensureVizaglandCopy(Property $property): void
    {
        if ($property->isOriginal()) {
            throw new RuntimeException('Original property records cannot be reviewed or modified.');
        }
    }

    /**
     * @param  list<PropertyStatus>  $allowedStatuses
     */
    private function ensureAllowedStatus(Property $property, array $allowedStatuses, PropertyReviewAction $action): void
    {
        if (in_array($property->property_status, $allowedStatuses, true)) {
            return;
        }

        $allowedLabels = implode(', ', array_map(
            static fn (PropertyStatus $status): string => $status->label(),
            $allowedStatuses,
        ));

        throw new RuntimeException(
            "Property cannot be {$action->label()} while status is {$property->property_status->label()}. Allowed statuses: {$allowedLabels}.",
        );
    }

    private function logPropertyReview(Property $property, string $action, string $label, User $admin): void
    {
        $referenceId = $property->property_reference_id ?? (string) $property->property_id;

        $this->activityLogService->log(
            type: ActivityLogType::PropertyReview,
            action: $action,
            description: "{$label} property {$referenceId}",
            entityType: 'property',
            entityId: $property->property_id,
            user: $admin,
            metadata: ['property_reference_id' => $referenceId],
        );
    }
}
