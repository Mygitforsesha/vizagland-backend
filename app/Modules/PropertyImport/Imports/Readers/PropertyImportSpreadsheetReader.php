<?php

namespace App\Modules\PropertyImport\Imports\Readers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class PropertyImportSpreadsheetReader
{
    /**
     * @return array{
     *     headers: list<string>,
     *     rows: list<array{row_number: int, values: list<scalar|null>}>
     * }
     */
    public function read(string $absolutePath, string $extension): array
    {
        $readerType = match (strtolower($extension)) {
            'csv' => 'Csv',
            'xls' => 'Xls',
            'xlsx' => 'Xlsx',
            default => throw new RuntimeException("Unsupported import file type: {$extension}"),
        };

        $reader = IOFactory::createReader($readerType);

        if ($readerType === 'Csv') {
            $reader->setDelimiter(',');
            $reader->setEnclosure('"');
            $reader->setSheetIndex(0);
        }

        $spreadsheet = $reader->load($absolutePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestDataColumn();

        if ($highestRow < 1) {
            throw new RuntimeException('The import file is empty.');
        }

        $headerRow = $worksheet->rangeToArray('A1:'.$highestColumn.'1', null, true, false)[0] ?? [];
        $headers = $this->normalizeHeaders($headerRow);

        if ($headers === []) {
            throw new RuntimeException('The import file must contain a header row.');
        }

        $rows = [];

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $rowValues = $worksheet->rangeToArray(
                'A'.$rowNumber.':'.$highestColumn.$rowNumber,
                null,
                true,
                false,
            )[0] ?? [];

            if ($this->isEmptyRow($rowValues)) {
                continue;
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'values' => array_map(
                    static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
                    $rowValues,
                ),
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return list<string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $header) {
            if (! is_string($header)) {
                continue;
            }

            $normalized = trim($header);

            if ($normalized === '') {
                continue;
            }

            $headers[] = $normalized;
        }

        return $headers;
    }

    /**
     * @param  list<mixed>  $rowValues
     */
    private function isEmptyRow(array $rowValues): bool
    {
        foreach ($rowValues as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
