<?php

namespace App\Modules\PropertyImport\Jobs;

use App\Modules\PropertyImport\Services\PropertyImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessPropertyImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(
        public readonly int $propertyImportJobId,
    ) {}

    public function handle(PropertyImportService $propertyImportService): void
    {
        $propertyImportService->process($this->propertyImportJobId);
    }

    public function failed(?Throwable $exception): void
    {
        report($exception);
    }
}
