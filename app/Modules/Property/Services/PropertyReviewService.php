<?php

namespace App\Modules\Property\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Enums\ReviewStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyReview;
use App\Modules\Property\Repositories\PropertyRepository;
use App\Modules\Property\Repositories\PropertyReviewRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PropertyReviewService
{
    public function __construct(
        private readonly PropertyRepository $propertyRepository,
        private readonly PropertyReviewRepository $propertyReviewRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->propertyReviewRepository->paginate($filters, $perPage);
    }

    public function approve(int $propertyId, User $reviewer, ?string $remarks): PropertyReview
    {
        return $this->processReview(
            propertyId: $propertyId,
            reviewer: $reviewer,
            reviewStatus: ReviewStatus::Approved,
            propertyStatus: PropertyStatus::Approved,
            remarks: $remarks,
            setPublishedAt: true,
        );
    }

    public function reject(int $propertyId, User $reviewer, ?string $remarks): PropertyReview
    {
        return $this->processReview(
            propertyId: $propertyId,
            reviewer: $reviewer,
            reviewStatus: ReviewStatus::Rejected,
            propertyStatus: PropertyStatus::Rejected,
            remarks: $remarks,
        );
    }

    public function requestChanges(int $propertyId, User $reviewer, ?string $remarks): PropertyReview
    {
        $review = $this->processReview(
            propertyId: $propertyId,
            reviewer: $reviewer,
            reviewStatus: ReviewStatus::NeedsRevision,
            propertyStatus: PropertyStatus::RequestChanges,
            remarks: $remarks,
        );

        $property = $this->propertyRepository->findById($propertyId);

        if ($property !== null) {
            $referenceId = $property->property_reference_id ?? (string) $property->property_id;
            $this->activityLogService->log(
                type: ActivityLogType::PropertyReview,
                action: 'request_changes',
                description: "Requested changes for property {$referenceId}",
                entityType: 'property',
                entityId: $property->property_id,
                user: $reviewer,
                metadata: ['property_reference_id' => $referenceId],
            );
        }

        return $review;
    }

    private function processReview(
        int $propertyId,
        User $reviewer,
        ReviewStatus $reviewStatus,
        PropertyStatus $propertyStatus,
        ?string $remarks,
        bool $setPublishedAt = false,
    ): PropertyReview {
        return DB::transaction(function () use ($propertyId, $reviewer, $reviewStatus, $propertyStatus, $remarks, $setPublishedAt) {
            $property = $this->propertyRepository->findById($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($property->isOriginal()) {
                throw new RuntimeException('Original property records cannot be reviewed.');
            }

            if ($property->property_status !== PropertyStatus::PendingReview) {
                throw new RuntimeException('Property is not pending review.');
            }

            $now = now();

            $propertyAttributes = [
                'property_status' => $propertyStatus,
                'property_reviewed_by' => $reviewer->user_id,
            ];

            if ($setPublishedAt) {
                $propertyAttributes['property_published_at'] = $now;
                $propertyAttributes['property_approved_at'] = $now;
                $propertyAttributes['property_approved_by_user_id'] = $reviewer->user_id;
                $propertyAttributes['property_verified'] = true;
            }

            if ($reviewStatus === ReviewStatus::Rejected && $remarks !== null) {
                $propertyAttributes['property_rejected_reason'] = $remarks;
            }

            if ($remarks !== null) {
                $propertyAttributes['property_review_remarks'] = $remarks;
            }

            $this->propertyReviewRepository->updateProperty($property, $propertyAttributes);

            return $this->propertyReviewRepository->create([
                'property_id' => $propertyId,
                'property_review_reviewed_by' => $reviewer->user_id,
                'property_review_status' => $reviewStatus,
                'property_review_comments' => $remarks,
                'property_review_reviewed_at' => $now,
            ]);
        });
    }
}
