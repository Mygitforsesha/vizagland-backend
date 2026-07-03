<?php

namespace App\Modules\MasterLocation\Services;

use App\Modules\MasterLocation\Repositories\MasterLocationRepository;
use RuntimeException;
use Throwable;

class MasterLocationImportService
{
    public function __construct(
        private readonly MasterLocationRepository $masterLocationRepository,
    ) {}

    /**
     * @return array{
     *     total_rows_read:int,
     *     inserted_rows:int,
     *     updated_rows:int,
     *     skipped_duplicate_rows:int,
     *     skipped_blank_rows:int,
     *     failed_rows:int
     * }
     */
    public function importFromCsv(string $path, bool $fresh = false): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Master location CSV file is not readable: {$path}");
        }

        if ($fresh) {
            $this->masterLocationRepository->truncate();
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open master location CSV file: {$path}");
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw new RuntimeException('Master location CSV file is empty.');
        }

        $now = now();
        $stats = [
            'total_rows_read' => 0,
            'inserted_rows' => 0,
            'updated_rows' => 0,
            'skipped_duplicate_rows' => 0,
            'skipped_blank_rows' => 0,
            'failed_rows' => 0,
        ];

        while (($record = fgetcsv($handle)) !== false) {
            $stats['total_rows_read']++;

            if ($this->isSkippableRow($record)) {
                $stats['skipped_blank_rows']++;

                continue;
            }

            $mapped = $this->mapCsvRow($record, $now);

            if ($mapped === null) {
                $stats['failed_rows']++;

                continue;
            }

            $comparisonAttributes = $this->comparisonAttributes($mapped);

            if ($this->masterLocationRepository->existsExactRecord($comparisonAttributes)) {
                $stats['skipped_duplicate_rows']++;

                continue;
            }

            try {
                $this->masterLocationRepository->create($mapped);
                $stats['inserted_rows']++;
            } catch (Throwable) {
                $stats['failed_rows']++;
            }
        }

        fclose($handle);

        return $stats;
    }

    /**
     * @param  list<string|null>  $record
     */
    private function isSkippableRow(array $record): bool
    {
        $nonEmptyValues = array_filter(
            $record,
            static fn (?string $value): bool => $value !== null && trim($value) !== '',
        );

        return $nonEmptyValues === [];
    }

    /**
     * @param  list<string|null>  $record
     * @return array<string, mixed>|null
     */
    private function mapCsvRow(array $record, \Illuminate\Support\Carbon $now): ?array
    {
        $village = $this->normalize($record[1] ?? null);

        if ($village === null) {
            return null;
        }

        return [
            'master_location_village' => $village,
            'master_location_nearby_location' => $this->normalize($record[2] ?? null),
            'master_location_district' => $this->normalize($record[3] ?? null),
            'master_location_mandal' => $this->normalize($record[4] ?? null),
            'master_location_panchayati' => $this->normalize($record[5] ?? null),
            'master_location_gvmc_zone' => $this->normalize($record[6] ?? null),
            'master_location_gvmc_ward' => $this->normalize($record[7] ?? null),
            'master_location_vmrda' => $this->normalize($record[8] ?? null),
            'master_location_registration_office' => $this->normalize($record[9] ?? null),
            'master_location_authority' => $this->normalize($record[10] ?? null),
            'master_location_additional_nearby_location' => $this->normalize($record[11] ?? null),
            'master_location_created_at' => $now,
            'master_location_updated_at' => $now,
        ];
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array<string, mixed>
     */
    private function comparisonAttributes(array $mapped): array
    {
        unset(
            $mapped['master_location_created_at'],
            $mapped['master_location_updated_at'],
        );

        return $mapped;
    }
}
