<?php

namespace App\Modules\Property\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Property\Enums\PropertyCreatedByType;
use App\Modules\Property\Enums\PropertyRecordType;
use App\Modules\Property\Enums\PropertySource;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Repositories\PropertyRepository;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PropertyService
{
    public function __construct(
        private readonly PropertyRepository $propertyRepository,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
        private readonly PropertyMediaStorage $propertyMediaStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage, string $sort): LengthAwarePaginator
    {
        return $this->propertyRepository->paginate($filters, $perPage, $sort);
    }

    public function listMyPropertiesByPhone(string $phoneNumber, int $perPage, string $sort): LengthAwarePaginator
    {
        $userId = User::query()
            ->where('user_phone', $phoneNumber)
            ->value('user_id');

        return $this->propertyRepository->paginateByCreator(
            userId: $userId !== null ? (int) $userId : null,
            phoneNumber: $phoneNumber,
            perPage: $perPage,
            sort: $sort,
        );
    }

    public function show(int $propertyId, User $user): Property
    {
        $property = $this->propertyRepository->findByIdWithDetails($propertyId);

        if ($property === null) {
            throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
        }

        $this->ensureUserCanViewProperty($property, $user);

        return $property;
    }

    private function ensureUserCanViewProperty(Property $property, User $user): void
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return;
        }

        if ($property->isOriginal()) {
            throw new RuntimeException('You do not have permission to view this property.');
        }

        // Employees and agents can open any Vizagland copy detail (same visibility as list).
        if ($user->isEmployee() || $user->isAgent()) {
            return;
        }

        throw new RuntimeException('You do not have permission to view this property.');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $propertyId, array $attributes): Property
    {
        $property = DB::transaction(function () use ($propertyId, $attributes) {
            $property = $this->propertyRepository->findById($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($property->isOriginal()) {
                throw new RuntimeException('Original property records cannot be modified.');
            }

            if ($attributes === []) {
                return $property;
            }

            return $this->propertyRepository->update($property, $attributes);
        });

        $referenceId = $property->property_reference_id ?? (string) $property->property_id;
        $this->activityLogService->log(
            type: ActivityLogType::Property,
            action: 'updated',
            description: "Updated property {$referenceId}",
            entityType: 'property',
            entityId: $property->property_id,
            metadata: ['property_reference_id' => $referenceId],
        );

        return $property;
    }

    public function submitForReview(int $propertyId, User $user): Property
    {
        $property = DB::transaction(function () use ($propertyId, $user) {
            $property = $this->propertyRepository->findById($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($property->isOriginal()) {
                throw new RuntimeException('Original property records cannot be submitted for review.');
            }

            if ($property->property_created_by !== $user->user_id) {
                throw new RuntimeException('You are not allowed to submit this property for review.');
            }

            if ($property->property_status !== PropertyStatus::Draft) {
                throw new RuntimeException('Only draft properties can be submitted for review.');
            }

            return $this->propertyRepository->update($property, [
                'property_status' => PropertyStatus::PendingReview,
                'property_submitted_at' => now(),
            ]);
        });

        $this->notificationService->notifyPropertySubmitted($property);

        $referenceId = $property->property_reference_id ?? (string) $property->property_id;
        $this->activityLogService->log(
            type: ActivityLogType::PropertyReview,
            action: 'submitted_for_review',
            description: "Submitted property {$referenceId} for review",
            entityType: 'property',
            entityId: $property->property_id,
            user: $user,
            metadata: ['property_reference_id' => $referenceId],
        );

        return $property;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     * @return array{
     *     original_property: Property,
     *     vizagland_copy_property: Property,
     *     property_reference_id: string,
     *     images_count: int,
     *     documents_count: int
     * }
     */
    public function createAuthenticated(array $data, array $images, array $documents, User $user, array $contactNumbers = []): array
    {
        return $this->createPropertyPair(
            $data,
            $images,
            $documents,
            $this->resolveCreatorContext($user),
            contactNumbers: $contactNumbers,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     * @param  array{username_or_mobile: string, password: string, email: ?string}|null  $authCredentials
     * @return array{
     *     original_property: Property,
     *     vizagland_copy_property: Property,
     *     property_reference_id: string,
     *     images_count: int,
     *     documents_count: int,
     *     username_or_mobile?: string
     * }
     */
    public function createPublic(
        array $data,
        array $images,
        array $documents,
        array $contactNumbers = [],
        ?array $authCredentials = null,
    ): array
    {
        return $this->createPropertyPair(
            $data,
            $images,
            $documents,
            $this->resolvePublicCreatorContext(),
            contactNumbers: $contactNumbers,
            authCredentials: $authCredentials,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     original_property: Property,
     *     vizagland_copy_property: Property,
     *     property_reference_id: string,
     *     images_count: int,
     *     documents_count: int
     * }
     */
    public function createFromBulkImport(array $data, User $user): array
    {
        return $this->createPropertyPair(
            $data,
            [],
            [],
            $this->resolveAdminImportCreatorContext($user),
            sendNotifications: false,
            logActivity: false,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     * @param  array{property_created_by_type: PropertyCreatedByType, property_created_by_id: ?int, property_created_by: ?int, property_source: PropertySource}  $creatorContext
     * @param  array{username_or_mobile: string, password: string, email: ?string}|null  $authCredentials
     * @return array{
     *     original_property: Property,
     *     vizagland_copy_property: Property,
     *     property_reference_id: string,
     *     images_count: int,
     *     documents_count: int,
     *     username_or_mobile?: string
     * }
     */
    private function createPropertyPair(
        array $data,
        array $images,
        array $documents,
        array $creatorContext,
        bool $sendNotifications = true,
        bool $logActivity = true,
        array $contactNumbers = [],
        ?array $authCredentials = null,
    ): array
    {
        $uploadedPaths = [];

        try {
            $result = DB::transaction(function () use ($data, $images, $documents, $creatorContext, $contactNumbers, $authCredentials, &$uploadedPaths) {
                $referenceId = $this->generatePropertyReferenceId();
                $baseAttributes = $this->buildPropertyAttributes($data, $creatorContext, $referenceId);

                $originalProperty = $this->propertyRepository->create([
                    ...$baseAttributes,
                    'property_record_type' => PropertyRecordType::Original,
                ]);

                $vizaglandCopyProperty = $this->propertyRepository->create([
                    ...$baseAttributes,
                    'property_record_type' => PropertyRecordType::VizaglandCopy,
                    'property_parent_property_id' => $originalProperty->property_id,
                ]);

                $imagesCount = $this->storeImagesForProperties(
                    [$originalProperty, $vizaglandCopyProperty],
                    $images,
                    $uploadedPaths,
                );
                $documentsCount = $this->storeDocumentsForProperties(
                    [$originalProperty, $vizaglandCopyProperty],
                    $documents,
                    $uploadedPaths,
                );
                $this->storeContactNumbersForProperties(
                    [$originalProperty, $vizaglandCopyProperty],
                    $contactNumbers,
                );

                $result = [
                    'original_property' => $originalProperty->fresh(['images', 'documents', 'contactNumbers']),
                    'vizagland_copy_property' => $vizaglandCopyProperty->fresh(['images', 'documents', 'contactNumbers']),
                    'property_reference_id' => $referenceId,
                    'images_count' => $imagesCount,
                    'documents_count' => $documentsCount,
                ];

                $authResult = $this->ensurePublicUserFromAuth($authCredentials, $data);

                if ($authResult !== null) {
                    $result['username_or_mobile'] = $authResult['username_or_mobile'];
                }

                $creatorUserId = $this->resolveLinkablePosterUserId($authResult, $data);

                if ($creatorUserId !== null) {
                    $this->attachPublicCreatorToProperties(
                        [$originalProperty, $vizaglandCopyProperty],
                        $creatorUserId,
                    );

                    $result['original_property'] = $originalProperty->fresh(['images', 'documents', 'contactNumbers']);
                    $result['vizagland_copy_property'] = $vizaglandCopyProperty->fresh(['images', 'documents', 'contactNumbers']);
                }

                return $result;
            });
        } catch (Throwable $exception) {
            $this->cleanupUploadedFiles($uploadedPaths);

            throw $exception;
        }

        if ($sendNotifications) {
            $this->notificationService->notifyPropertyCreated($result['vizagland_copy_property']);
        }

        if ($logActivity) {
            $property = $result['vizagland_copy_property'];
            $referenceId = $result['property_reference_id'];
            $this->activityLogService->log(
                type: ActivityLogType::Property,
                action: 'created',
                description: "Created property {$referenceId}",
                entityType: 'property',
                entityId: $property->property_id,
                metadata: ['property_reference_id' => $referenceId],
            );
        }

        return $result;
    }

    /**
     * Create a public_user from property_auth when mobile + password are present.
     * If the phone already exists, skip create (do not disturb existing accounts) and still return the mobile.
     * Returns user_id when the account is (or becomes) a public_user or agent so properties can be linked.
     *
     * @param  array{username_or_mobile: string, password: string, email: ?string}|null  $authCredentials
     * @param  array<string, mixed>  $propertyData
     * @return array{username_or_mobile: string, user_id: ?int}|null
     */
    private function ensurePublicUserFromAuth(?array $authCredentials, array $propertyData): ?array
    {
        if ($authCredentials === null) {
            return null;
        }

        $mobile = $authCredentials['username_or_mobile'];

        $existing = User::query()->where('user_phone', $mobile)->first();

        if ($existing !== null) {
            return [
                'username_or_mobile' => $mobile,
                'user_id' => $this->isLinkablePosterRole($existing->user_role) ? $existing->user_id : null,
            ];
        }

        $email = $authCredentials['email'];

        if ($email !== null && User::query()->where('user_email', $email)->exists()) {
            $email = null;
        }

        $ownerName = $propertyData['property_owner_name'] ?? null;
        $fullName = is_string($ownerName) && trim($ownerName) !== ''
            ? trim($ownerName)
            : 'Public User';

        $user = User::query()->create([
            'user_full_name' => $fullName,
            'user_phone' => $mobile,
            'user_email' => $email,
            'user_password' => $authCredentials['password'],
            'user_role' => UserRole::PublicUser,
            'user_is_active' => true,
        ]);

        return [
            'username_or_mobile' => $mobile,
            'user_id' => $user->user_id,
        ];
    }

    /**
     * Resolve which public_user/agent should own a public-posted property.
     * Prefers property_auth linkage, then falls back to property_owner_phone.
     *
     * @param  array{username_or_mobile: string, user_id: ?int}|null  $authResult
     * @param  array<string, mixed>  $propertyData
     */
    private function resolveLinkablePosterUserId(?array $authResult, array $propertyData): ?int
    {
        if ($authResult !== null && $authResult['user_id'] !== null) {
            return $authResult['user_id'];
        }

        $ownerPhone = $propertyData['property_owner_phone'] ?? null;

        if (! is_string($ownerPhone) || trim($ownerPhone) === '') {
            return null;
        }

        $user = User::query()
            ->where('user_phone', trim($ownerPhone))
            ->whereIn('user_role', [UserRole::PublicUser, UserRole::Agent])
            ->where('user_is_active', true)
            ->first();

        return $user?->user_id;
    }

    private function isLinkablePosterRole(UserRole $role): bool
    {
        return $role === UserRole::PublicUser || $role === UserRole::Agent;
    }

    /**
     * Link public-posted properties to the public_user without changing created_by_type (stays Public).
     *
     * @param  list<Property>  $properties
     */
    private function attachPublicCreatorToProperties(array $properties, int $userId): void
    {
        foreach ($properties as $property) {
            $this->propertyRepository->update($property, [
                'property_created_by' => $userId,
                'property_created_by_id' => $userId,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{property_created_by_type: PropertyCreatedByType, property_created_by_id: ?int, property_created_by: ?int, property_source: PropertySource}  $creatorContext
     * @return array<string, mixed>
     */
    private function buildPropertyAttributes(array $data, array $creatorContext, string $referenceId): array
    {
        unset(
            $data['property_status'],
            $data['property_verified'],
            $data['property_reference_id'],
            $data['property_submitted_at'],
            $data['property_approved_at'],
            $data['property_approved_by_user_id'],
            $data['property_record_type'],
            $data['property_parent_property_id'],
            $data['property_is_featured'],
            $data['property_view_count'],
            $data['property_lead_count'],
            $data['property_is_deleted'],
            $data['property_assigned_user_id'],
            $data['property_review_remarks'],
            $data['property_rejected_reason'],
        );

        return [
            ...$data,
            'property_reference_id' => $referenceId,
            'property_status' => PropertyStatus::Draft,
            'property_verified' => false,
            'property_is_featured' => false,
            'property_view_count' => 0,
            'property_lead_count' => 0,
            'property_is_deleted' => false,
            'property_assigned_user_id' => null,
            'property_review_remarks' => null,
            'property_rejected_reason' => null,
            'property_created_by_type' => $creatorContext['property_created_by_type'],
            'property_created_by_id' => $creatorContext['property_created_by_id'],
            'property_created_by' => $creatorContext['property_created_by'],
            'property_source' => $creatorContext['property_source'],
        ];
    }

    /**
     * @param  list<Property>  $properties
     * @param  list<UploadedFile>  $images
     * @param  list<string>  $uploadedPaths
     */
    private function storeImagesForProperties(array $properties, array $images, array &$uploadedPaths): int
    {
        foreach ($images as $index => $image) {
            $path = $this->propertyMediaStorage->storeImage($image);
            $uploadedPaths[] = $path;

            $imageAttributes = [
                'property_image_path' => $path,
                'property_image_original_name' => $image->getClientOriginalName(),
                'property_image_size' => $image->getSize(),
                'property_image_mime_type' => $image->getMimeType(),
                'property_image_sort_order' => $index,
            ];

            foreach ($properties as $property) {
                $this->propertyRepository->createImage($property->property_id, $imageAttributes);
            }
        }

        return count($images);
    }

    /**
     * @param  list<Property>  $properties
     * @param  list<UploadedFile>  $documents
     * @param  list<string>  $uploadedPaths
     */
    private function storeDocumentsForProperties(array $properties, array $documents, array &$uploadedPaths): int
    {
        foreach ($documents as $document) {
            $path = $this->propertyMediaStorage->storeDocument($document);
            $uploadedPaths[] = $path;

            $documentAttributes = [
                'property_document_original_name' => $document->getClientOriginalName(),
                'property_document_mime_type' => $document->getMimeType(),
                'property_document_path' => $path,
                'property_document_size' => $document->getSize(),
            ];

            foreach ($properties as $property) {
                $this->propertyRepository->createDocument($property->property_id, $documentAttributes);
            }
        }

        return count($documents);
    }

    /**
     * @param  list<Property>  $properties
     * @param  list<array{property_contact_number_registration_type: ?string, property_contact_number_phone_number: ?string}>  $contactNumbers
     */
    private function storeContactNumbersForProperties(array $properties, array $contactNumbers): void
    {
        if ($contactNumbers === []) {
            return;
        }

        foreach ($properties as $property) {
            foreach ($contactNumbers as $contactNumber) {
                $this->propertyRepository->createContactNumber($property->property_id, $contactNumber);
            }
        }
    }

    /**
     * @return array{property_created_by_type: PropertyCreatedByType, property_created_by_id: int, property_created_by: int, property_source: PropertySource}
     */
    private function resolveCreatorContext(User $user): array
    {
        if ($user->isEmployee()) {
            return [
                'property_created_by_type' => PropertyCreatedByType::Employee,
                'property_created_by_id' => $user->user_id,
                'property_created_by' => $user->user_id,
                'property_source' => PropertySource::Employee,
            ];
        }

        return [
            'property_created_by_type' => PropertyCreatedByType::Agent,
            'property_created_by_id' => $user->user_id,
            'property_created_by' => $user->user_id,
            'property_source' => PropertySource::Agent,
        ];
    }

    /**
     * @return array{property_created_by_type: PropertyCreatedByType, property_created_by_id: null, property_created_by: null, property_source: PropertySource}
     */
    private function resolvePublicCreatorContext(): array
    {
        return [
            'property_created_by_type' => PropertyCreatedByType::Public,
            'property_created_by_id' => null,
            'property_created_by' => null,
            'property_source' => PropertySource::Public,
        ];
    }

    /**
     * @return array{property_created_by_type: PropertyCreatedByType, property_created_by_id: int, property_created_by: int, property_source: PropertySource}
     */
    private function resolveAdminImportCreatorContext(User $user): array
    {
        return [
            'property_created_by_type' => PropertyCreatedByType::Employee,
            'property_created_by_id' => $user->user_id,
            'property_created_by' => $user->user_id,
            'property_source' => PropertySource::Admin,
        ];
    }

    private function generatePropertyReferenceId(): string
    {
        do {
            $referenceId = 'VL-'.strtoupper(Str::random(8));
        } while (Property::query()->where('property_reference_id', $referenceId)->exists());

        return $referenceId;
    }

    /**
     * @param  list<string>  $uploadedPaths
     */
    private function cleanupUploadedFiles(array $uploadedPaths): void
    {
        foreach ($uploadedPaths as $path) {
            $this->propertyMediaStorage->delete($path);
        }
    }
}
