<?php

namespace App\Modules\Property\Services;

use App\Modules\Property\Enums\PropertyCreatedByType;
use App\Modules\Property\Enums\PropertySource;
use App\Modules\Property\Enums\PropertyStatus;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Repositories\PropertyRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PropertyService
{
    public function __construct(
        private readonly PropertyRepository $propertyRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage, string $sort): LengthAwarePaginator
    {
        return $this->propertyRepository->paginate($filters, $perPage, $sort);
    }

    public function show(int $propertyId): Property
    {
        $property = $this->propertyRepository->findByIdWithDetails($propertyId);

        if ($property === null) {
            throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
        }

        return $property;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $propertyId, array $attributes): Property
    {
        return DB::transaction(function () use ($propertyId, $attributes) {
            $property = $this->propertyRepository->findById($propertyId);

            if ($property === null) {
                throw (new ModelNotFoundException)->setModel(Property::class, [$propertyId]);
            }

            if ($attributes === []) {
                return $property;
            }

            return $this->propertyRepository->update($property, $attributes);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     * @return array{property: Property, images_count: int, documents_count: int}
     */
    public function createAuthenticated(array $data, array $images, array $documents, User $user): array
    {
        return $this->createProperty(
            $data,
            $images,
            $documents,
            $this->resolveCreatorContext($user),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     * @return array{property: Property, images_count: int, documents_count: int}
     */
    public function createPublic(array $data, array $images, array $documents): array
    {
        return $this->createProperty($data, $images, $documents, $this->resolvePublicCreatorContext());
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<UploadedFile>  $documents
     * @param  array{property_created_by_type: PropertyCreatedByType, property_created_by_id: ?int, property_created_by: ?int, property_source: PropertySource}  $creatorContext
     * @return array{property: Property, images_count: int, documents_count: int}
     */
    private function createProperty(array $data, array $images, array $documents, array $creatorContext): array
    {
        $uploadedPaths = [];

        try {
            return DB::transaction(function () use ($data, $images, $documents, $creatorContext, &$uploadedPaths) {
                $property = $this->propertyRepository->create(
                    $this->buildPropertyAttributes($data, $creatorContext),
                );

                $imagesCount = $this->storeImages($property, $images, $uploadedPaths);
                $documentsCount = $this->storeDocuments($property, $documents, $uploadedPaths);

                return [
                    'property' => $property->fresh(),
                    'images_count' => $imagesCount,
                    'documents_count' => $documentsCount,
                ];
            });
        } catch (Throwable $exception) {
            $this->cleanupUploadedFiles($uploadedPaths);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{property_created_by_type: PropertyCreatedByType, property_created_by_id: ?int, property_created_by: ?int, property_source: PropertySource}  $creatorContext
     * @return array<string, mixed>
     */
    private function buildPropertyAttributes(array $data, array $creatorContext): array
    {
        $attributes = collect($data)
            ->except(['property_images', 'property_documents'])
            ->merge([
                'property_code' => $data['property_code'] ?? $this->generatePropertyCode(),
                'property_status' => PropertyStatus::Draft,
                'property_created_by_type' => $creatorContext['property_created_by_type'],
                'property_created_by_id' => $creatorContext['property_created_by_id'],
                'property_created_by' => $creatorContext['property_created_by'],
                'property_source' => $creatorContext['property_source'],
            ])
            ->toArray();

        return $attributes;
    }

    /**
     * @param  list<UploadedFile>  $images
     * @param  list<string>  $uploadedPaths
     */
    private function storeImages(Property $property, array $images, array &$uploadedPaths): int
    {
        foreach ($images as $index => $image) {
            $path = $image->store('properties/images', 'public');
            $uploadedPaths[] = $path;

            $this->propertyRepository->createImage($property->property_id, [
                'property_image_path' => $path,
                'property_image_name' => $image->getClientOriginalName(),
                'property_image_size' => $image->getSize(),
                'property_image_sort_order' => $index,
            ]);
        }

        return count($images);
    }

    /**
     * @param  list<UploadedFile>  $documents
     * @param  list<string>  $uploadedPaths
     */
    private function storeDocuments(Property $property, array $documents, array &$uploadedPaths): int
    {
        foreach ($documents as $document) {
            $path = $document->store('properties/documents', 'public');
            $uploadedPaths[] = $path;

            $this->propertyRepository->createDocument($property->property_id, [
                'property_document_name' => $document->getClientOriginalName(),
                'property_document_type' => strtolower($document->getClientOriginalExtension()),
                'property_document_path' => $path,
                'property_document_size' => $document->getSize(),
            ]);
        }

        return count($documents);
    }

    /**
     * @return array{property_created_by_type: PropertyCreatedByType, property_created_by_id: int, property_created_by: int, property_source: PropertySource}
     */
    private function resolveCreatorContext(User $user): array
    {
        if ($user->isEmployee()) {
            return [
                'property_created_by_type' => PropertyCreatedByType::Employee,
                'property_created_by_id' => $user->id,
                'property_created_by' => $user->id,
                'property_source' => PropertySource::Employee,
            ];
        }

        return [
            'property_created_by_type' => PropertyCreatedByType::Agent,
            'property_created_by_id' => $user->id,
            'property_created_by' => $user->id,
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

    private function generatePropertyCode(): string
    {
        do {
            $code = 'VL-'.strtoupper(Str::random(8));
        } while (Property::query()->where('property_code', $code)->exists());

        return $code;
    }

    /**
     * @param  list<string>  $uploadedPaths
     */
    private function cleanupUploadedFiles(array $uploadedPaths): void
    {
        foreach ($uploadedPaths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
