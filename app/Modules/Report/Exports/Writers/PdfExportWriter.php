<?php

namespace App\Modules\Report\Exports\Writers;

class PdfExportWriter
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<scalar|null>>  $rows
     */
    public function write(string $path, array $headers, iterable $rows, string $title = 'Export'): void
    {
        $lines = [$title, str_repeat('-', 80)];

        $lines[] = implode(' | ', $headers);
        $lines[] = str_repeat('-', 80);

        foreach ($rows as $row) {
            $lines[] = implode(' | ', array_map(
                static fn (mixed $value): string => $value === null ? '' : (string) $value,
                $row,
            ));
        }

        $content = implode("\n", $lines);
        $pdf = $this->buildPdf($content);

        if (file_put_contents($path, $pdf) === false) {
            throw new \RuntimeException('Unable to create PDF export file.');
        }
    }

    private function buildPdf(string $text): string
    {
        $escaped = $this->escapePdfText($text);
        $lines = explode("\n", $escaped);
        $streamLines = ["BT", "/F1 9 Tf", "50 780 Td", "14 TL"];

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                $streamLines[] = '('.$line.') Tj';
            } else {
                $streamLines[] = 'T*';
                $streamLines[] = '('.$line.') Tj';
            }
        }

        $streamLines[] = 'ET';
        $stream = implode("\n", $streamLines);
        $streamLength = strlen($stream);

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objects[] = "<< /Length {$streamLength} >>\nstream\n{$stream}\nendstream";
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $objectNumber = $index + 1;
            $pdf .= "{$objectNumber} 0 obj\n{$object}\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref'."\n";
        $pdf .= '0 '.(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= 'trailer'."\n";
        $pdf .= '<< /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= 'startxref'."\n";
        $pdf .= $xrefPosition."\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
