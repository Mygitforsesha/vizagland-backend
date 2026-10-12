<?php

namespace App\Modules\Advertisement\Resources;

use App\Modules\Advertisement\Models\Advertisement;
use App\Modules\Advertisement\Models\AdvertisementDocument;
use App\Modules\Advertisement\Models\AdvertisementImage;
use App\Modules\Advertisement\Services\AdvertisementMediaStorage;

class AdvertisementMediaFields
{
    /**
     * @return array<string, mixed>
     */
    public static function forAdvertisement(Advertisement $advertisement): array
    {
        $storage = app(AdvertisementMediaStorage::class);
        $images = $advertisement->relationLoaded('images')
            ? $advertisement->images
            : $advertisement->images()->get();
        $documents = $advertisement->relationLoaded('documents')
            ? $advertisement->documents
            : $advertisement->documents()->get();

        $imagePayload = $images
            ->map(static fn (AdvertisementImage $image): array => [
                'advertisement_image_id' => $image->advertisement_image_id,
                'advertisement_image_original_name' => $image->advertisement_image_original_name,
                'advertisement_image_url' => $storage->url($image->advertisement_image_path),
                'advertisement_image_size' => $image->advertisement_image_size,
                'advertisement_image_mime_type' => $image->advertisement_image_mime_type,
            ])
            ->values()
            ->all();

        if ($imagePayload === [] && filled($advertisement->advertisement_image_path)) {
            $imagePayload[] = [
                'advertisement_image_id' => null,
                'advertisement_image_original_name' => null,
                'advertisement_image_url' => $storage->url($advertisement->advertisement_image_path),
                'advertisement_image_size' => null,
                'advertisement_image_mime_type' => null,
            ];
        }

        return [
            'advertisement_image_url' => $imagePayload[0]['advertisement_image_url'] ?? $storage->url($advertisement->advertisement_image_path),
            'advertisement_images' => $imagePayload,
            'advertisement_documents' => $documents
                ->map(static fn (AdvertisementDocument $document): array => [
                    'advertisement_document_id' => $document->advertisement_document_id,
                    'advertisement_document_original_name' => $document->advertisement_document_original_name,
                    'advertisement_document_url' => $storage->url($document->advertisement_document_path),
                    'advertisement_document_size' => $document->advertisement_document_size,
                    'advertisement_document_mime_type' => $document->advertisement_document_mime_type,
                ])
                ->values()
                ->all(),
            'advertisement_youtube_video_links' => self::links(
                $advertisement->advertisement_youtube_video_links,
                $advertisement->property_details,
                'property_youtube_video_links',
                'property_youtube_video_link',
            ),
            'advertisement_location_links' => self::links(
                $advertisement->advertisement_location_links,
                $advertisement->property_details,
                'property_location_links',
                'property_location_link',
            ),
        ];
    }

    /**
     * @param  mixed  $stored
     * @param  mixed  $details
     * @return list<array{url: string}>
     */
    private static function links(mixed $stored, mixed $details, string $listKey, string $legacyKey): array
    {
        $links = self::normalize($stored);

        if ($links !== [] || ! is_array($details)) {
            return $links;
        }

        $links = self::normalize($details[$listKey] ?? null);

        if ($links === [] && isset($details[$legacyKey])) {
            $links = self::normalize($details[$legacyKey]);
        }

        return $links;
    }

    /**
     * @return list<array{url: string}>
     */
    private static function normalize(mixed $items): array
    {
        if (is_string($items) && trim($items) !== '') {
            return [['url' => trim($items)]];
        }

        if (! is_array($items)) {
            return [];
        }

        $links = [];

        foreach ($items as $item) {
            if (is_string($item) && trim($item) !== '') {
                $links[] = ['url' => trim($item)];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $url = $item['url'] ?? null;

            if (is_string($url) && trim($url) !== '') {
                $links[] = ['url' => trim($url)];
            }
        }

        return $links;
    }
}
