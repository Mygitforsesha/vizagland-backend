<?php

namespace App\Modules\Report\Exports\Writers;

use ZipArchive;

class XlsxExportWriter
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<scalar|null>>  $rows
     */
    public function write(string $path, array $headers, iterable $rows): void
    {
        $sheetRows = [$headers];

        foreach ($rows as $row) {
            $sheetRows[] = array_map(
                static fn (mixed $value): string => $value === null ? '' : (string) $value,
                $row,
            );
        }

        $sheetXml = $this->buildSheetXml($sheetRows);
        $sharedStrings = $this->collectSharedStrings($sheetRows);
        $sharedStringsXml = $this->buildSharedStringsXml($sharedStrings);
        $stringIndex = array_flip($sharedStrings);

        $sheetXml = $this->buildSheetXml($sheetRows, $stringIndex);

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create XLSX export file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<string, int>|null  $stringIndex
     */
    private function buildSheetXml(array $rows, ?array $stringIndex = null): string
    {
        $sheetData = '';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $cells = '';

            foreach ($row as $columnIndex => $value) {
                $cellRef = $this->columnLetter($columnIndex).$rowNumber;
                $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

                if ($stringIndex !== null) {
                    $index = $stringIndex[$value] ?? 0;
                    $cells .= '<c r="'.$cellRef.'" t="s"><v>'.$index.'</v></c>';
                } else {
                    $cells .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
                }
            }

            $sheetData .= '<row r="'.$rowNumber.'">'.$cells.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.$sheetData.'</sheetData>'
            .'</worksheet>';
    }

    /**
     * @param  list<list<string>>  $rows
     * @return list<string>
     */
    private function collectSharedStrings(array $rows): array
    {
        $strings = [];

        foreach ($rows as $row) {
            foreach ($row as $value) {
                if (! in_array($value, $strings, true)) {
                    $strings[] = $value;
                }
            }
        }

        return $strings;
    }

    /**
     * @param  list<string>  $strings
     */
    private function buildSharedStringsXml(array $strings): string
    {
        $items = '';

        foreach ($strings as $string) {
            $items .= '<si><t>'.htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">'
            .$items
            .'</sst>';
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        $column = $index + 1;

        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $letter = chr(65 + $remainder).$letter;
            $column = intdiv($column - 1, 26);
        }

        return $letter;
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>
XML;
    }

    private function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Export" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>
XML;
    }

    private function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>
  <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
  <borders count="1"><border/></borders>
  <cellStyleXfs count="1"><xf/></cellStyleXfs>
  <cellXfs count="1"><xf xfId="0"/></cellXfs>
</styleSheet>
XML;
    }
}
