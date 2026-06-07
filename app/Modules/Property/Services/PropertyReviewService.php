<?php

namespace App\Modules\Property\Services;

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
        return $this->processReview(
            propertyId: $propertyId,
            reviewer: $reviewer,
            reviewStatus: ReviewStatus::NeedsRevision,
            propertyStatus: PropertyStatus::Draft,
            remarks: $remarks,
        );
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

            if (! in_array($property->property_status, [PropertyStatus::PendingReview, PropertyStatus::Draft], true)) {
                throw new RuntimeException('Property is not eligible for review.');
            }

            $now = now();

            $propertyAttributes = [
                'property_status' => $propertyStatus,
                'property_reviewed_by' => $reviewer->id,
            ];

            if ($setPublishedAt) {
                $propertyAttributes['property_published_at'] = $now;
            }

            $this->propertyReviewRepository->updateProperty($property, $propertyAttributes);

            return $this->propertyReviewRepository->create([
                'property_id' => $propertyId,
                'reviewed_by' => $reviewer->id,
                'review_status' => $reviewStatus,
                'review_comments' => $remarks,
                'reviewed_at' => $now,
            ]);
        });
    }
}
