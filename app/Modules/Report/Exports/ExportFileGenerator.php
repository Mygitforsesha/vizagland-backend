<?php

namespace App\Modules\Report\Exports;

use App\Modules\Report\Enums\ExportFormat;
use App\Modules\Report\Exports\Writers\CsvExportWriter;
use App\Modules\Report\Exports\Writers\PdfExportWriter;
use App\Modules\Report\Exports\Writers\XlsxExportWriter;

class ExportFileGenerator
{
    public function __construct(
        private readonly CsvExportWriter $csvExportWriter,
        private readonly XlsxExportWriter $xlsxExportWriter,
        private readonly PdfExportWriter $pdfExportWriter,
    ) {}

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<scalar|null>>  $rows
     */
    public function generate(
        string $path,
        ExportFormat $format,
        array $headers,
        iterable $rows,
        string $title = 'Export',
    ): void {
        match ($format) {
            ExportFormat::Csv => $this->csvExportWriter->write($path, $headers, $rows),
            ExportFormat::Xlsx => $this->xlsxExportWriter->write($path, $headers, $rows),
            ExportFormat::Pdf => $this->pdfExportWriter->write($path, $headers, $rows, $title),
        };
    }
}
