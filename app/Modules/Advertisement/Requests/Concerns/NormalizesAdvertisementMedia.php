<?php

namespace App\Modules\Advertisement\Requests\Concerns;

use Illuminate\Http\UploadedFile;

trait NormalizesAdvertisementMedia
{
    protected function mergeAdvertisementMediaInput(): void
    {
        $input = [];

        foreach ([
            'property_other_services',
            'advertisement_youtube_video_links',
            'advertisement_location_links',
            'property_youtube_video_links',
            'property_location_links',
        ] as $key) {
            $decoded = $this->decodedArrayInput($key);

            if ($decoded !== null) {
                $input[$key] = $decoded;
            }
        }

        if ($input !== []) {
            $this->merge($input);
        }

        $youtube = $this->collectedLinks('youtube');
        $location = $this->collectedLinks('location');

        $normalized = [];

        if ($youtube !== null) {
            $normalized['advertisement_youtube_video_links'] = $youtube;
        }

        if ($location !== null) {
            $normalized['advertisement_location_links'] = $location;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function advertisementMediaRules(): array
    {
        return [
            'advertisement_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'advertisement_images' => ['nullable', 'array', 'max:30'],
            'advertisement_images.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'property_images' => ['nullable', 'array', 'max:30'],
            'property_images.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'advertisement_documents' => ['nullable', 'array', 'max:30'],
            'advertisement_documents.*' => ['file', 'mimes:pdf,doc,docx,zip,jpg,jpeg,png,webp', 'max:10240'],
            'property_documents' => ['nullable', 'array', 'max:30'],
            'property_documents.*' => ['file', 'mimes:pdf,doc,docx,zip,jpg,jpeg,png,webp', 'max:10240'],
            'advertisement_youtube_video_links' => ['nullable', 'array', 'max:10'],
            'advertisement_youtube_video_links.*.url' => ['required', 'string', 'max:2048', 'url'],
            'advertisement_location_links' => ['nullable', 'array', 'max:10'],
            'advertisement_location_links.*.url' => ['required', 'string', 'max:2048', 'url'],
        ];
    }

    /**
     * @return list<UploadedFile>
     */
    public function uploadedImages(): array
    {
        return array_values(array_merge(
            $this->uploadedFiles('advertisement_image'),
            $this->uploadedFiles('advertisement_images'),
            $this->uploadedFiles('property_images'),
        ));
    }

    /**
     * @return list<UploadedFile>
     */
    public function uploadedDocuments(): array
    {
        return array_values(array_merge(
            $this->uploadedFiles('advertisement_documents'),
            $this->uploadedFiles('property_documents'),
        ));
    }

    public function youtubeLinksWereSent(): bool
    {
        return $this->linkSourcesWereSent('youtube');
    }

    public function locationLinksWereSent(): bool
    {
        return $this->linkSourcesWereSent('location');
    }

    /**
     * @return list<array{url: string}>
     */
    public function normalizedYoutubeLinks(): array
    {
        return $this->normalizeLinkItems($this->input('advertisement_youtube_video_links'));
    }

    /**
     * @return list<array{url: string}>
     */
    public function normalizedLocationLinks(): array
    {
        return $this->normalizeLinkItems($this->input('advertisement_location_links'));
    }

    /**
     * @return list<array{url: string}>|null
     */
    private function collectedLinks(string $kind): ?array
    {
        if (! $this->linkSourcesWereSent($kind)) {
            return null;
        }

        $services = $this->input('property_other_services');
        $details = $this->input('property_details');
        $services = is_array($services) ? $services : [];
        $details = is_array($details) ? $details : [];

        if ($kind === 'youtube') {
            $items = $this->input('advertisement_youtube_video_links')
                ?? $this->input('property_youtube_video_links')
                ?? ($services['property_youtube_video_links'] ?? null)
                ?? ($details['property_youtube_video_links'] ?? null);
            $legacy = $this->input('advertisement_youtube_video_link')
                ?? $this->input('property_youtube_video_link')
                ?? ($services['property_youtube_video_link'] ?? null)
                ?? ($details['property_youtube_video_link'] ?? null);
        } else {
            $items = $this->input('advertisement_location_links')
                ?? $this->input('property_location_links')
                ?? ($services['property_location_links'] ?? null)
                ?? ($details['property_location_links'] ?? null);
            $legacy = $this->input('advertisement_location_link')
                ?? $this->input('property_location_link')
                ?? ($services['property_location_link'] ?? null)
                ?? ($details['property_location_link'] ?? null);
        }

        return $this->normalizeLinkItems($items, $legacy);
    }

    private function linkSourcesWereSent(string $kind): bool
    {
        $keys = $kind === 'youtube'
            ? [
                'advertisement_youtube_video_links',
                'advertisement_youtube_video_link',
                'property_youtube_video_links',
                'property_youtube_video_link',
            ]
            : [
                'advertisement_location_links',
                'advertisement_location_link',
                'property_location_links',
                'property_location_link',
            ];

        foreach ($keys as $key) {
            if ($this->exists($key)) {
                return true;
            }
        }

        $services = $this->input('property_other_services');
        $details = $this->input('property_details');

        $nestedKeys = $kind === 'youtube'
            ? ['property_youtube_video_links', 'property_youtube_video_link']
            : ['property_location_links', 'property_location_link'];

        foreach ([$services, $details] as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ($nestedKeys as $nestedKey) {
                if (array_key_exists($nestedKey, $group)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<array{url: string}>
     */
    private function normalizeLinkItems(mixed $items, mixed $legacySingular = null): array
    {
        $links = [];

        if (is_array($items)) {
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
        } elseif (is_string($items) && trim($items) !== '') {
            $links[] = ['url' => trim($items)];
        }

        if ($links === [] && is_string($legacySingular) && trim($legacySingular) !== '') {
            $links[] = ['url' => trim($legacySingular)];
        }

        return $links;
    }

    /**
     * @return list<UploadedFile>
     */
    private function uploadedFiles(string $key): array
    {
        $files = $this->file($key);

        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn (mixed $file): bool => $file instanceof UploadedFile,
        ));
    }

    /**
     * @return array<mixed>|null
     */
    private function decodedArrayInput(string $key): ?array
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
