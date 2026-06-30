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
            $imported = $this->masterLocationImportService->importFromCsv(
                path: $path,
                fresh: $fresh,
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Imported {$imported} master location record(s).");

        return self::SUCCESS;
    }
}
