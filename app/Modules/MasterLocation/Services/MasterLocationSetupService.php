<?php

namespace App\Modules\MasterLocation\Services;

use App\Modules\MasterLocation\Repositories\MasterLocationRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MasterLocationSetupService
{
    private const MIGRATION_PATH = 'database/migrations/2026_06_29_100000_create_master_locations_table.php';

    public function __construct(
        private readonly MasterLocationRepository $masterLocationRepository,
        private readonly MasterLocationImportService $masterLocationImportService,
    ) {}

    public function tableExists(): bool
    {
        return Schema::hasTable('master_locations');
    }

    public function ensureTableExists(): void
    {
        if ($this->tableExists()) {
            return;
        }

        Artisan::call('migrate', [
            '--path' => self::MIGRATION_PATH,
            '--force' => true,
        ]);
    }

    public function hasData(): bool
    {
        if (! $this->tableExists()) {
            return false;
        }

        return $this->masterLocationRepository->count() > 0;
    }

    public function isReady(): bool
    {
        return $this->tableExists();
    }

    /**
     * @return array{
     *     table_created: bool,
     *     import_stats: array{
     *         total_rows_read:int,
     *         inserted_rows:int,
     *         updated_rows:int,
     *         skipped_duplicate_rows:int,
     *         skipped_blank_rows:int,
     *         failed_rows:int
     *     },
     *     total: int
     * }
     */
    public function setup(bool $freshImport = false): array
    {
        $tableCreated = ! $this->tableExists();

        $this->ensureTableExists();

        $importStats = [
            'total_rows_read' => 0,
            'inserted_rows' => 0,
            'updated_rows' => 0,
            'skipped_duplicate_rows' => 0,
            'skipped_blank_rows' => 0,
            'failed_rows' => 0,
        ];

        if ($freshImport || ! $this->hasData()) {
            $path = base_path('docs/village_master.csv');
            $importStats = $this->masterLocationImportService->importFromCsv(
                path: $path,
                fresh: $freshImport,
            );
        }

        return [
            'table_created' => $tableCreated,
            'import_stats' => $importStats,
            'total' => $this->masterLocationRepository->count(),
        ];
    }
}
