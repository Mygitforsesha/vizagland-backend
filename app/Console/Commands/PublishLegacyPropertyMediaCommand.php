<?php

namespace App\Console\Commands;

use App\Modules\Property\Models\PropertyDocument;
use App\Modules\Property\Models\PropertyImage;
use App\Modules\Property\Services\PropertyMediaStorage;
use Illuminate\Console\Command;

class PublishLegacyPropertyMediaCommand extends Command
{
    protected $signature = 'property-media:publish-legacy';

    protected $description = 'Copy legacy property media from storage/app/public into public/storage for direct web access';

    public function __construct(
        private readonly PropertyMediaStorage $propertyMediaStorage,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $paths = PropertyImage::query()
            ->whereNotNull('property_image_path')
            ->pluck('property_image_path')
            ->merge(
                PropertyDocument::query()
                    ->whereNotNull('property_document_path')
                    ->pluck('property_document_path'),
            )
            ->filter()
            ->unique()
            ->values();

        $published = 0;

        foreach ($paths as $path) {
            $url = $this->propertyMediaStorage->url($path);

            if ($url !== null) {
                $published++;
            }
        }

        $this->info("Published {$published} property media file(s) to the public web root.");

        return self::SUCCESS;
    }
}
