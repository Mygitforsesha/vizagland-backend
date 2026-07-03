<?php

namespace App\Console\Commands;

use App\Modules\MasterLocation\Services\MasterLocationImportService;
use App\Modules\MasterLocation\Services\MasterLocationSetupService;
use Illuminate\Console\Command;
use Throwable;

class ImportMasterLocationsCommand extends Command
{
    protected $signature = 'master-locations:import
                            {--path= : Absolute or relative path to the CSV file}
                            {--fresh : Truncate existing master locations before importing}';

    protected $description = 'Import master village/area locations from the CSV dataset';

    public function __construct(
        private readonly MasterLocationImportService $masterLocationImportService,
        private readonly MasterLocationSetupService $masterLocationSetupService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('docs/village_master.csv');
        $fresh = (bool) $this->option('fresh');

        try {
            $this->masterLocationSetupService->ensureTableExists();
            $stats = $this->masterLocationImportService->importFromCsv(
                path: $path,
                fresh: $fresh,
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Total rows read: {$stats['total_rows_read']}");
        $this->info("Inserted rows: {$stats['inserted_rows']}");
        $this->info("Updated rows: {$stats['updated_rows']}");
        $this->info("Skipped duplicate rows: {$stats['skipped_duplicate_rows']}");
        $this->info("Skipped blank rows: {$stats['skipped_blank_rows']}");
        $this->info("Failed rows: {$stats['failed_rows']}");

        return self::SUCCESS;
    }
}
