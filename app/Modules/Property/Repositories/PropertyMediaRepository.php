<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Models\PropertyDocument;
use App\Modules\Property\Models\PropertyImage;

class PropertyMediaRepository
{
    public function countImages(int $propertyId): int
    {
        return PropertyImage::query()
            ->where('property_id', $propertyId)
            ->count();
    }

    public function countDocuments(int $propertyId): int
    {
        return PropertyDocument::query()
            ->where('property_id', $propertyId)
            ->count();
    }

    public function findImageById(int $propertyImageId): ?PropertyImage
    {
        return PropertyImage::query()
            ->where('property_image_id', $propertyImageId)
            ->first();
    }

    public function findDocumentById(int $propertyDocumentId): ?PropertyDocument
    {
        return PropertyDocument::query()
            ->where('property_document_id', $propertyDocumentId)
            ->first();
    }

    public function getNextImageSortOrder(int $propertyId): int
    {
        return (int) PropertyImage::query()
            ->where('property_id', $propertyId)
            ->max('property_image_sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createImage(int $propertyId, array $attributes): PropertyImage
    {
        return PropertyImage::query()->create([
            'property_id' => $propertyId,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createDocument(int $propertyId, array $attributes): PropertyDocument
    {
        return PropertyDocument::query()->create([
            'property_id' => $propertyId,
            ...$attributes,
        ]);
    }

    public function deleteImage(PropertyImage $image): void
    {
        $image->delete();
    }

    public function deleteDocument(PropertyDocument $document): void
    {
        $document->delete();
    }
}
