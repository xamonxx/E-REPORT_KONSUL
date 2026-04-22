<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;

class AnalyticsExcelExporter
{
    public function buildWorkbook(array $report): string
    {
        $worksheets = [
            $this->buildSummarySheet($report),
            $this->buildMetricSheet(
                'Status',
                'Distribusi status konsultasi pada periode terpilih.',
                $report['statusDistribution'] ?? collect(),
                [50, 220, 90, 90, 110],
                includeColor: true
            ),
            $this->buildMetricSheet(
                'Kebutuhan',
                'Distribusi kategori kebutuhan lead.',
                $report['needsDistribution'] ?? collect(),
                [50, 260, 90, 90],
            ),
            $this->buildRegionSheet($report),
            $this->buildRankingSheet($report),
            $this->buildRawDataSheet($report['rawRows'] ?? collect(), $report),
        ];

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<?mso-application progid="Excel.Sheet"?>',
            '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:html="http://www.w3.org/TR/REC-html40">',
            $this->stylesXml(),
        ];

        foreach ($worksheets as $worksheet) {
            $xml[] = sprintf('<Worksheet ss:Name="%s">', $this->escapeSheetName($worksheet['name']));
            $xml[] = '<Table x:FullColumns="1" x:FullRows="1">';

            foreach ($worksheet['columns'] as $width) {
                $xml[] = sprintf('<Column ss:AutoFitWidth="0" ss:Width="%s"/>', (float) $width);
            }

            foreach ($worksheet['rows'] as $row) {
                $rowHeight = isset($row['height']) ? sprintf(' ss:Height="%s"', (float) $row['height']) : '';
                $xml[] = sprintf('<Row%s>', $rowHeight);

                foreach ($row['cells'] as $cell) {
                    $xml[] = $this->buildCell($cell);
                }

                $xml[] = '</Row>';
            }

            $xml[] = '</Table>';
            $xml[] = $this->worksheetOptionsXml($worksheet['freeze_rows'] ?? 1);
            $xml[] = '</Worksheet>';
        }

        $xml[] = '</Workbook>';

        return implode('', $xml);
    }

    private function buildSummarySheet(array $report): array
    {
        $columns = [175, 190, 175, 150];
        $lastIndex = count($columns) - 1;

        $rows = [
            $this->row([
                $this->cell('Laporan Analisis Konsultasi', 'sheetTitle', mergeAcross: $lastIndex),
            ], 28),
            $this->row([
                $this->cell(
                    sprintf(
                        'Periode %s | Akun %s | Generated %s',
                        $report['periodLabel'] ?? '-',
                        $report['selectedAccountName'] ?? 'Semua Akun',
                        now()->format('d/m/Y H:i')
                    ),
                    'sheetSubtitle',
                    mergeAcross: $lastIndex
                ),
            ], 22),
            $this->blankRow($lastIndex + 1),
            $this->row([
                $this->cell('Parameter Laporan', 'sectionTitle', mergeAcross: $lastIndex),
            ]),
            $this->row([
                $this->cell('Periode', 'metaLabel'),
                $this->cell($report['periodLabel'] ?? '-', 'metaValue'),
                $this->cell('Pembanding', 'metaLabel'),
                $this->cell($report['comparisonLabel'] ?? '-', 'metaValue'),
            ]),
            $this->row([
                $this->cell('Akun', 'metaLabel'),
                $this->cell($report['selectedAccountName'] ?? 'Semua Akun', 'metaValue'),
                $this->cell('Tipe Periode', 'metaLabel'),
                $this->cell(ucfirst($report['period']['type'] ?? '-'), 'metaValue'),
            ]),
            $this->blankRow($lastIndex + 1),
            $this->row([
                $this->cell('KPI Utama', 'sectionTitle', mergeAcross: $lastIndex),
            ]),
            $this->row([
                $this->cell('Total Konsultasi', 'kpiLabel'),
                $this->cell($report['totalLeads'] ?? 0, 'kpiValueNumber', 'Number'),
                $this->cell('Total Survey', 'kpiLabel'),
                $this->cell($report['totalSurveys'] ?? 0, 'kpiValueNumber', 'Number'),
            ], 24),
            $this->row([
                $this->cell('Total Deal', 'kpiLabel'),
                $this->cell($report['totalDeals'] ?? 0, 'kpiValueNumber', 'Number'),
                $this->cell('Konversi Survey', 'kpiLabel'),
                $this->cell(($report['conversionRate'] ?? 0) / 100, 'percentStrong', 'Number'),
            ], 24),
            $this->row([
                $this->cell('Growth vs Pembanding', 'kpiLabel'),
                $this->cell(($report['growthPercent'] ?? 0) / 100, 'percentStrong', 'Number'),
                $this->cell('Data Mentah', 'kpiLabel'),
                $this->cell(($report['rawRows'] ?? collect())->count(), 'kpiValueNumber', 'Number'),
            ], 24),
            $this->blankRow($lastIndex + 1),
            $this->row([
                $this->cell('Insight Otomatis', 'sectionTitle', mergeAcross: $lastIndex),
            ]),
        ];

        foreach (($report['insights'] ?? collect()) as $index => $insight) {
            $rows[] = $this->row([
                $this->cell($index + 1, 'tableCellCenter', 'Number'),
                $this->cell($insight, $index % 2 === 0 ? 'tableCellWrap' : 'tableCellWrapAlt', mergeAcross: 2),
            ], 34);
        }

        return [
            'name' => 'Ringkasan',
            'columns' => $columns,
            'rows' => $rows,
            'freeze_rows' => 4,
        ];
    }

    private function buildMetricSheet(
        string $sheetName,
        string $subtitle,
        Collection $items,
        array $columns,
        bool $includeColor = false
    ): array {
        $total = $items->sum('count') ?: 1;
        $rows = $this->sheetIntroRows($sheetName, $subtitle, count($columns));

        $headerCells = [
            $this->cell('No', 'tableHeader'),
            $this->cell($sheetName === 'Status' ? 'Status' : 'Kategori', 'tableHeader'),
            $this->cell('Jumlah', 'tableHeader'),
            $this->cell('Persentase', 'tableHeader'),
        ];

        if ($includeColor) {
            $headerCells[] = $this->cell('Warna', 'tableHeader');
        }

        $rows[] = $this->row($headerCells, 22);

        foreach ($items->values() as $index => $item) {
            $base = $index % 2 === 0 ? '' : 'Alt';
            $row = [
                $this->cell($index + 1, 'tableCellCenter' . $base, 'Number'),
                $this->cell($item['name'] ?? '-', 'tableCell' . $base),
                $this->cell($item['count'] ?? 0, 'tableNumber' . $base, 'Number'),
                $this->cell(($item['count'] ?? 0) / $total, 'tablePercent' . $base, 'Number'),
            ];

            if ($includeColor) {
                $row[] = $this->cell($item['color'] ?? '-', 'tableCell' . $base);
            }

            $rows[] = $this->row($row, 20);
        }

        if ($items->isEmpty()) {
            $rows[] = $this->row([
                $this->cell('Tidak ada data pada periode ini.', 'emptyState', mergeAcross: count($columns) - 1),
            ], 24);
        }

        return [
            'name' => $sheetName,
            'columns' => $columns,
            'rows' => $rows,
            'freeze_rows' => 4,
        ];
    }

    private function buildRegionSheet(array $report): array
    {
        $rows = $this->sheetIntroRows(
            'Wilayah',
            'Distribusi provinsi, kota, dan segmen Jawa Barat.',
            5
        );

        $sections = [
            [
                'title' => 'Top Provinsi',
                'items' => $report['provinceDistribution'] ?? collect(),
                'label' => 'Provinsi',
                'show_percentage' => true,
            ],
            [
                'title' => 'Top Kota / Kabupaten',
                'items' => $report['cityDistribution'] ?? collect(),
                'label' => 'Kota / Kabupaten',
                'show_percentage' => true,
            ],
            [
                'title' => 'Segmen Jawa Barat',
                'items' => $report['westJavaSegmentDistribution'] ?? collect(),
                'label' => 'Segmen',
                'show_percentage' => false,
            ],
        ];

        foreach ($sections as $sectionIndex => $section) {
            if ($sectionIndex > 0) {
                $rows[] = $this->blankRow(5);
            }

            $rows[] = $this->row([
                $this->cell($section['title'], 'sectionTitle', mergeAcross: 4),
            ]);

            $header = [
                $this->cell('No', 'tableHeader'),
                $this->cell($section['label'], 'tableHeader'),
                $this->cell('Jumlah', 'tableHeader'),
            ];

            if ($section['show_percentage']) {
                $header[] = $this->cell('Persentase', 'tableHeader');
                $header[] = $this->cell('Catatan', 'tableHeader');
            } else {
                $header[] = $this->cell('Warna', 'tableHeader');
                $header[] = $this->cell('Keterangan', 'tableHeader');
            }

            $rows[] = $this->row($header, 22);

            $total = collect($section['items'])->sum('count') ?: 1;

            foreach (collect($section['items'])->values() as $index => $item) {
                $base = $index % 2 === 0 ? '' : 'Alt';

                $cells = [
                    $this->cell($index + 1, 'tableCellCenter' . $base, 'Number'),
                    $this->cell($item['name'] ?? '-', 'tableCell' . $base),
                    $this->cell($item['count'] ?? 0, 'tableNumber' . $base, 'Number'),
                ];

                if ($section['show_percentage']) {
                    $cells[] = $this->cell(($item['percentage'] ?? 0) / 100, 'tablePercent' . $base, 'Number');
                    $cells[] = $this->cell('Top distribusi periode', 'tableCell' . $base);
                } else {
                    $cells[] = $this->cell($item['color'] ?? '-', 'tableCell' . $base);
                    $cells[] = $this->cell(
                        $total > 0 ? round((($item['count'] ?? 0) / $total) * 100, 1) . '% kontribusi' : '-',
                        'tableCell' . $base
                    );
                }

                $rows[] = $this->row($cells, 20);
            }

            if (collect($section['items'])->isEmpty()) {
                $rows[] = $this->row([
                    $this->cell('Tidak ada data wilayah pada periode ini.', 'emptyState', mergeAcross: 4),
                ], 24);
            }
        }

        return [
            'name' => 'Wilayah',
            'columns' => [50, 220, 90, 90, 170],
            'rows' => $rows,
            'freeze_rows' => 4,
        ];
    }

    private function buildRankingSheet(array $report): array
    {
        $rows = $this->sheetIntroRows(
            'Ranking',
            'Perbandingan performa akun dan admin pada periode laporan.',
            5
        );

        $rows[] = $this->row([
            $this->cell('Ranking Akun', 'sectionTitle', mergeAcross: 4),
        ]);
        $rows[] = $this->row([
            $this->cell('No', 'tableHeader'),
            $this->cell('Akun', 'tableHeader'),
            $this->cell('Total', 'tableHeader'),
            $this->cell('Survey', 'tableHeader'),
            $this->cell('Rasio', 'tableHeader'),
        ], 22);

        foreach (($report['accountRanking'] ?? collect())->values() as $index => $item) {
            $base = $index % 2 === 0 ? '' : 'Alt';
            $rows[] = $this->row([
                $this->cell($index + 1, 'tableCellCenter' . $base, 'Number'),
                $this->cell($item['name'] ?? '-', 'tableCell' . $base),
                $this->cell($item['total'] ?? 0, 'tableNumber' . $base, 'Number'),
                $this->cell($item['surveys'] ?? 0, 'tableNumber' . $base, 'Number'),
                $this->cell(($item['rate'] ?? 0) / 100, 'tablePercent' . $base, 'Number'),
            ], 20);
        }

        if (($report['accountRanking'] ?? collect())->isEmpty()) {
            $rows[] = $this->row([
                $this->cell('Tidak ada data ranking akun.', 'emptyState', mergeAcross: 4),
            ], 24);
        }

        $rows[] = $this->blankRow(5);
        $rows[] = $this->row([
            $this->cell('Ranking Admin', 'sectionTitle', mergeAcross: 4),
        ]);
        $rows[] = $this->row([
            $this->cell('No', 'tableHeader'),
            $this->cell('Admin', 'tableHeader'),
            $this->cell('Akun', 'tableHeader'),
            $this->cell('Total Lead', 'tableHeader'),
            $this->cell('Catatan', 'tableHeader'),
        ], 22);

        foreach (($report['adminRanking'] ?? collect())->values() as $index => $item) {
            $base = $index % 2 === 0 ? '' : 'Alt';
            $rows[] = $this->row([
                $this->cell($index + 1, 'tableCellCenter' . $base, 'Number'),
                $this->cell($item['name'] ?? '-', 'tableCell' . $base),
                $this->cell($item['account'] ?? '-', 'tableCell' . $base),
                $this->cell($item['total'] ?? 0, 'tableNumber' . $base, 'Number'),
                $this->cell('Volume input periode aktif', 'tableCell' . $base),
            ], 20);
        }

        if (($report['adminRanking'] ?? collect())->isEmpty()) {
            $rows[] = $this->row([
                $this->cell('Tidak ada data ranking admin.', 'emptyState', mergeAcross: 4),
            ], 24);
        }

        return [
            'name' => 'Ranking',
            'columns' => [50, 180, 170, 90, 180],
            'rows' => $rows,
            'freeze_rows' => 4,
        ];
    }

    private function buildRawDataSheet(Collection $rows, array $report): array
    {
        $columns = [110, 165, 110, 105, 120, 130, 130, 120, 230, 95, 120, 115];
        $sheetRows = $this->sheetIntroRows(
            'Data Mentah',
            sprintf(
                'Lampiran lengkap data konsultasi untuk %s - %s.',
                $report['selectedAccountName'] ?? 'Semua Akun',
                $report['periodLabel'] ?? '-'
            ),
            count($columns)
        );

        $sheetRows[] = $this->row([
            $this->cell('ID Konsultasi', 'tableHeader'),
            $this->cell('Nama Klien', 'tableHeader'),
            $this->cell('No Telepon', 'tableHeader'),
            $this->cell('Provinsi', 'tableHeader'),
            $this->cell('Kota', 'tableHeader'),
            $this->cell('Akun', 'tableHeader'),
            $this->cell('Kebutuhan', 'tableHeader'),
            $this->cell('Status', 'tableHeader'),
            $this->cell('Catatan', 'tableHeader'),
            $this->cell('Tgl Konsultasi', 'tableHeader'),
            $this->cell('Dibuat Oleh', 'tableHeader'),
            $this->cell('Update', 'tableHeader'),
        ], 24);

        foreach ($rows->values() as $index => $row) {
            $base = $index % 2 === 0 ? '' : 'Alt';
            $sheetRows[] = $this->row([
                $this->cell($row['consultation_id'] ?? '', 'tableCell' . $base),
                $this->cell($row['client_name'] ?? '', 'tableCell' . $base),
                $this->cell($row['phone'] ?? '', 'tableCell' . $base),
                $this->cell($row['province'] ?? '', 'tableCell' . $base),
                $this->cell($row['city'] ?? '', 'tableCell' . $base),
                $this->cell($row['account'] ?? '', 'tableCell' . $base),
                $this->cell($row['need'] ?? '', 'tableCell' . $base),
                $this->cell($row['status'] ?? '', 'tableCell' . $base),
                $this->cell($row['notes'] ?? '', 'tableCellWrap' . $base),
                $this->cell($row['consultation_date'] ?? '', 'tableCellCenter' . $base),
                $this->cell($row['creator'] ?? '', 'tableCell' . $base),
                $this->cell($row['updated_at'] ?? '', 'tableCellCenter' . $base),
            ], 34);
        }

        if ($rows->isEmpty()) {
            $sheetRows[] = $this->row([
                $this->cell('Tidak ada data mentah pada periode ini.', 'emptyState', mergeAcross: count($columns) - 1),
            ], 24);
        }

        return [
            'name' => 'Data Mentah',
            'columns' => $columns,
            'rows' => $sheetRows,
            'freeze_rows' => 4,
        ];
    }

    private function sheetIntroRows(string $title, string $subtitle, int $columnCount): array
    {
        $lastIndex = $columnCount - 1;

        return [
            $this->row([
                $this->cell($title, 'sheetTitle', mergeAcross: $lastIndex),
            ], 28),
            $this->row([
                $this->cell($subtitle, 'sheetSubtitle', mergeAcross: $lastIndex),
            ], 22),
            $this->blankRow($columnCount),
        ];
    }

    private function row(array $cells, ?int $height = null): array
    {
        return [
            'cells' => $cells,
            'height' => $height,
        ];
    }

    private function blankRow(int $columnCount): array
    {
        return $this->row([
            $this->cell('', 'blank', mergeAcross: $columnCount - 1),
        ], 10);
    }

    private function cell(
        mixed $value,
        ?string $style = null,
        string $type = 'String',
        ?int $mergeAcross = null
    ): array {
        return [
            'value' => $value,
            'style' => $style,
            'type' => $type,
            'merge_across' => $mergeAcross,
        ];
    }

    private function buildCell(array $cell): string
    {
        $attributes = [];

        if (! empty($cell['style'])) {
            $attributes[] = sprintf('ss:StyleID="%s"', $cell['style']);
        }

        if (($cell['merge_across'] ?? null) !== null) {
            $attributes[] = sprintf('ss:MergeAcross="%d"', (int) $cell['merge_across']);
        }

        $type = $cell['type'] ?? 'String';
        $value = $cell['value'] ?? '';

        $data = sprintf(
            '<Data ss:Type="%s">%s</Data>',
            $type,
            htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8')
        );

        return sprintf('<Cell %s>%s</Cell>', implode(' ', $attributes), $data);
    }

    private function worksheetOptionsXml(int $freezeRows): string
    {
        return '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">'
            . '<FreezePanes/>'
            . '<FrozenNoSplit/>'
            . sprintf('<SplitHorizontal>%d</SplitHorizontal>', max(1, $freezeRows))
            . sprintf('<TopRowBottomPane>%d</TopRowBottomPane>', max(1, $freezeRows))
            . '<ActivePane>2</ActivePane>'
            . '<ProtectObjects>False</ProtectObjects>'
            . '<ProtectScenarios>False</ProtectScenarios>'
            . '</WorksheetOptions>';
    }

    private function escapeSheetName(string $name): string
    {
        $normalized = mb_substr(preg_replace('/[\\\\\\/?*\\[\\]:]/', '-', $name), 0, 31);

        return htmlspecialchars($normalized, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function stylesXml(): string
    {
        return '<Styles>'
            . '<Style ss:ID="Default" ss:Name="Normal">'
            . '<Alignment ss:Vertical="Center"/>'
            . '<Borders>'
            . '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>'
            . '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>'
            . '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>'
            . '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>'
            . '</Borders>'
            . '<Font ss:FontName="Calibri" ss:Size="11" ss:Color="#0F172A"/>'
            . '<Interior/>'
            . '<NumberFormat/>'
            . '<Protection/>'
            . '</Style>'
            . '<Style ss:ID="sheetTitle">'
            . '<Font ss:Bold="1" ss:Size="18" ss:Color="#0F172A"/>'
            . '<Alignment ss:Vertical="Center"/>'
            . '<Interior ss:Color="#DBEAFE" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="sheetSubtitle">'
            . '<Font ss:Size="11" ss:Color="#475569"/>'
            . '<Alignment ss:Vertical="Center" ss:WrapText="1"/>'
            . '<Interior ss:Color="#EFF6FF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="sectionTitle">'
            . '<Font ss:Bold="1" ss:Size="12" ss:Color="#1E3A8A"/>'
            . '<Interior ss:Color="#E0ECFF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="metaLabel">'
            . '<Font ss:Bold="1" ss:Color="#0F172A"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="metaValue">'
            . '<Alignment ss:WrapText="1"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="kpiLabel">'
            . '<Font ss:Bold="1" ss:Color="#1E293B"/>'
            . '<Interior ss:Color="#EFF6FF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="kpiValueNumber">'
            . '<Font ss:Bold="1" ss:Size="13" ss:Color="#1D4ED8"/>'
            . '<Alignment ss:Horizontal="Right"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '<NumberFormat ss:Format="Standard"/>'
            . '</Style>'
            . '<Style ss:ID="percentStrong">'
            . '<Font ss:Bold="1" ss:Size="13" ss:Color="#0F766E"/>'
            . '<Alignment ss:Horizontal="Right"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '<NumberFormat ss:Format="0.0%"/>'
            . '</Style>'
            . '<Style ss:ID="tableHeader">'
            . '<Font ss:Bold="1" ss:Color="#FFFFFF"/>'
            . '<Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>'
            . '<Interior ss:Color="#1D4ED8" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableCell">'
            . '<Alignment ss:Vertical="Top" ss:WrapText="1"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableCellAlt">'
            . '<Alignment ss:Vertical="Top" ss:WrapText="1"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableCellWrap">'
            . '<Alignment ss:Vertical="Top" ss:WrapText="1"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableCellWrapAlt">'
            . '<Alignment ss:Vertical="Top" ss:WrapText="1"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableCellCenter">'
            . '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableCellCenterAlt">'
            . '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="tableNumber">'
            . '<Alignment ss:Horizontal="Right" ss:Vertical="Center"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '<NumberFormat ss:Format="#,##0"/>'
            . '</Style>'
            . '<Style ss:ID="tableNumberAlt">'
            . '<Alignment ss:Horizontal="Right" ss:Vertical="Center"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '<NumberFormat ss:Format="#,##0"/>'
            . '</Style>'
            . '<Style ss:ID="tablePercent">'
            . '<Alignment ss:Horizontal="Right" ss:Vertical="Center"/>'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '<NumberFormat ss:Format="0.0%"/>'
            . '</Style>'
            . '<Style ss:ID="tablePercentAlt">'
            . '<Alignment ss:Horizontal="Right" ss:Vertical="Center"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '<NumberFormat ss:Format="0.0%"/>'
            . '</Style>'
            . '<Style ss:ID="emptyState">'
            . '<Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>'
            . '<Font ss:Italic="1" ss:Color="#64748B"/>'
            . '<Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>'
            . '</Style>'
            . '<Style ss:ID="blank">'
            . '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>'
            . '</Style>'
            . '</Styles>';
    }
}
