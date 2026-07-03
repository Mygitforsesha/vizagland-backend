<?php

namespace App\Console\Commands;

use App\Modules\MasterLocation\Services\MasterLocationSetupService;
use Illuminate\Console\Command;
use Throwable;

class SetupMasterLocationsCommand extends Command
{
    protected $signature = 'master-locations:setup
                            {--fresh : Truncate and re-import all master location records}';

    protected $description = 'Create the master_locations table and import the CSV dataset if needed';

    public function __construct(
        private readonly MasterLocationSetupService $masterLocationSetupService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fresh = (bool) $this->option('fresh');

        try {
            $result = $this->masterLocationSetupService->setup(freshImport: $fresh);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($result['table_created']) {
            $this->info('Created master_locations table.');
        } else {
            $this->info('master_locations table already exists.');
        }

        $stats = $result['import_stats'];

        if ($stats['total_rows_read'] > 0) {
            $this->info("Total rows read: {$stats['total_rows_read']}");
            $this->info("Inserted rows: {$stats['inserted_rows']}");
            $this->info("Updated rows: {$stats['updated_rows']}");
            $this->info("Skipped duplicate rows: {$stats['skipped_duplicate_rows']}");
            $this->info("Skipped blank rows: {$stats['skipped_blank_rows']}");
            $this->info("Failed rows: {$stats['failed_rows']}");
        } else {
            $this->info('No import was needed; master location data is already present.');
        }

        $this->info("Total master location records: {$result['total']}.");

        return self::SUCCESS;
    }
}
