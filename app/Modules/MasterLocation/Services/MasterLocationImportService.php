<?php

namespace App\Modules\MasterLocation\Services;

use App\Modules\MasterLocation\Repositories\MasterLocationRepository;
use RuntimeException;

class MasterLocationImportService
{
    private const CHUNK_SIZE = 200;

    public function __construct(
        private readonly MasterLocationRepository $masterLocationRepository,
    ) {}

    public function importFromCsv(string $path, bool $fresh = false): int
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
        $rows = [];
        $imported = 0;

        while (($record = fgetcsv($handle)) !== false) {
            if ($this->isSkippableRow($record)) {
                continue;
            }

            $mapped = $this->mapCsvRow($record, $now);

            if ($mapped === null) {
                continue;
            }

            $rows[] = $mapped;

            if (count($rows) >= self::CHUNK_SIZE) {
                $this->masterLocationRepository->insertMany($rows);
                $imported += count($rows);
                $rows = [];
            }
        }

        fclose($handle);

        if ($rows !== []) {
            $this->masterLocationRepository->insertMany($rows);
            $imported += count($rows);
        }

        return $imported;
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
}
