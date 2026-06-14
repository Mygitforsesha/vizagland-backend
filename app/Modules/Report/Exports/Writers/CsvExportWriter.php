<?php

namespace App\Modules\Report\Exports\Writers;

class CsvExportWriter
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<scalar|null>>  $rows
     */
    public function write(string $path, array $headers, iterable $rows): void
    {
        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Unable to create CSV export file.');
        }

        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }
}
