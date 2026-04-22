<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisis</title>
    <style>
        @page {
            margin: 20px 22px 26px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.42;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr, td, th {
            page-break-inside: avoid;
        }

        .cover {
            background: #f8fbff;
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 18px;
        }

        .eyebrow {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.6px;
            color: #1d4ed8;
            font-weight: 700;
        }

        .title {
            font-size: 24px;
            font-weight: 800;
            margin: 7px 0 4px;
        }

        .subtitle {
            color: #475569;
            font-size: 11px;
        }

        .meta-grid {
            margin-top: 16px;
        }

        .meta-grid td {
            padding: 8px 10px;
            border: 1px solid #dbeafe;
            background: #ffffff;
        }

        .meta-grid .label {
            width: 110px;
            color: #64748b;
            font-weight: 700;
            background: #eff6ff;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .section-note {
            color: #64748b;
            font-size: 9px;
            margin-top: -2px;
            margin-bottom: 8px;
        }

        .cards td {
            width: 25%;
            padding-right: 8px;
            vertical-align: top;
        }

        .cards td:last-child {
            padding-right: 0;
        }

        .card {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: #ffffff;
            padding: 12px;
            min-height: 72px;
        }

        .card-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }

        .card-value {
            margin-top: 8px;
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
        }

        .panel {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            padding: 12px;
        }

        .insight-list {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .insight-list li {
            margin-bottom: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 9px;
            font-weight: 700;
        }

        .two-up td {
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }

        .two-up td:last-child {
            padding-right: 0;
        }

        .metric-row {
            margin-bottom: 9px;
        }

        .metric-head {
            font-size: 9px;
            margin-bottom: 4px;
        }

        .metric-name {
            font-weight: 700;
        }

        .metric-count {
            float: right;
            color: #475569;
        }

        .track {
            height: 7px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .fill {
            height: 7px;
            border-radius: 999px;
        }

        .report-table {
            margin-top: 8px;
            table-layout: fixed;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #dbe4e7;
            padding: 7px 8px;
            vertical-align: top;
            word-break: break-word;
        }

        .report-table th {
            background: #1d4ed8;
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            text-align: left;
        }

        .report-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .muted {
            color: #64748b;
        }

        .small {
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="cover">
        <div class="eyebrow">Laporan Analisis</div>
        <div class="title">Rekap Analisa Konsultasi</div>
        <div class="subtitle">Ringkasan performa konsultasi, distribusi kebutuhan, wilayah, dan ranking operasional dalam satu dokumen yang siap dibagikan.</div>

        <table class="meta-grid">
            <tr>
                <td class="label">Periode</td>
                <td>{{ $periodLabel }}</td>
                <td class="label">Pembanding</td>
                <td>{{ $comparisonLabel }}</td>
            </tr>
            <tr>
                <td class="label">Akun</td>
                <td>{{ $selectedAccountName }}</td>
                <td class="label">Generated</td>
                <td>{{ $generatedAt->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Eksekutif</div>
        <table class="cards">
            <tr>
                <td><div class="card"><div class="card-label">Total Konsultasi</div><div class="card-value">{{ number_format($totalLeads) }}</div></div></td>
                <td><div class="card"><div class="card-label">Total Survey</div><div class="card-value">{{ number_format($totalSurveys) }}</div></div></td>
                <td><div class="card"><div class="card-label">Total Deal</div><div class="card-value">{{ number_format($totalDeals) }}</div></div></td>
                <td><div class="card"><div class="card-label">Konversi Survey</div><div class="card-value">{{ $conversionRate }}%</div></div></td>
            </tr>
        </table>

        <div class="panel" style="margin-top: 10px;">
            <span class="badge">Growth {{ $growthPercent }}%</span>
            <span class="muted small" style="margin-left: 8px;">dibanding {{ $comparisonLabel }}</span>
            <ul class="insight-list">
                @foreach($insights as $insight)
                    <li>{{ $insight }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="section">
        <table class="two-up">
            <tr>
                <td>
                    <div class="panel">
                        <div class="section-title" style="font-size: 13px; margin-bottom: 6px;">Distribusi Status</div>
                        @php $statusMax = $statusDistribution->max('count') ?: 1; @endphp
                        @foreach($statusDistribution as $item)
                            <div class="metric-row">
                                <div class="metric-head">
                                    <span class="metric-name">{{ $item['name'] }}</span>
                                    <span class="metric-count">{{ $item['count'] }}</span>
                                </div>
                                <div class="track">
                                    <div class="fill" style="width: {{ ($item['count'] / $statusMax) * 100 }}%; background: {{ $item['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="panel">
                        <div class="section-title" style="font-size: 13px; margin-bottom: 6px;">Kategori Kebutuhan</div>
                        @php $needsMax = $needsDistribution->max('count') ?: 1; @endphp
                        @forelse($needsDistribution as $item)
                            <div class="metric-row">
                                <div class="metric-head">
                                    <span class="metric-name">{{ $item['name'] }}</span>
                                    <span class="metric-count">{{ $item['count'] }}</span>
                                </div>
                                <div class="track">
                                    <div class="fill" style="width: {{ ($item['count'] / $needsMax) * 100 }}%; background: #2563eb;"></div>
                                </div>
                            </div>
                        @empty
                            <div class="muted">Belum ada data kategori kebutuhan pada periode ini.</div>
                        @endforelse
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if($provinceDistribution->isNotEmpty() || $cityDistribution->isNotEmpty() || $accountRanking->isNotEmpty() || $adminRanking->isNotEmpty())
    <div class="section">
        <div class="section-title">Analisa Wilayah dan Ranking</div>
        <div class="section-note">Tabel dipisah per topik agar lebih mudah dibaca saat dibuka dalam PDF.</div>

        <table class="two-up">
            <tr>
                <td>
                    <div class="panel">
                        <div class="section-title" style="font-size: 13px; margin-bottom: 4px;">Top Provinsi</div>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">No</th>
                                    <th style="width: 52%;">Provinsi</th>
                                    <th style="width: 20%;">Jumlah</th>
                                    <th style="width: 20%;">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($provinceDistribution as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-right">{{ $item['count'] }}</td>
                                        <td class="text-right">{{ $item['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="muted">Belum ada data provinsi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="panel">
                        <div class="section-title" style="font-size: 13px; margin-bottom: 4px;">Top Kota / Kabupaten</div>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">No</th>
                                    <th style="width: 52%;">Kota / Kabupaten</th>
                                    <th style="width: 20%;">Jumlah</th>
                                    <th style="width: 20%;">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cityDistribution as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-right">{{ $item['count'] }}</td>
                                        <td class="text-right">{{ $item['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="muted">Belum ada data kota / kabupaten.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="two-up" style="margin-top: 8px;">
            <tr>
                <td>
                    <div class="panel">
                        <div class="section-title" style="font-size: 13px; margin-bottom: 4px;">Segmen Jawa Barat</div>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">No</th>
                                    <th style="width: 46%;">Segmen</th>
                                    <th style="width: 18%;">Jumlah</th>
                                    <th style="width: 28%;">Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalWestJava = $westJavaSegmentDistribution->sum('count'); @endphp
                                @forelse($westJavaSegmentDistribution as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-right">{{ $item['count'] }}</td>
                                        <td class="text-right">{{ $totalWestJava > 0 ? round(($item['count'] / $totalWestJava) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="muted">Belum ada data segmen Jawa Barat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="panel">
                        <div class="section-title" style="font-size: 13px; margin-bottom: 4px;">Ranking Akun</div>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">No</th>
                                    <th style="width: 42%;">Akun</th>
                                    <th style="width: 16%;">Total</th>
                                    <th style="width: 16%;">Survey</th>
                                    <th style="width: 18%;">Rasio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountRanking as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-right">{{ $item['total'] }}</td>
                                        <td class="text-right">{{ $item['surveys'] }}</td>
                                        <td class="text-right">{{ $item['rate'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="muted">Belum ada data ranking akun.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="panel" style="margin-top: 8px;">
            <div class="section-title" style="font-size: 13px; margin-bottom: 4px;">Ranking Admin</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 34%;">Admin</th>
                        <th style="width: 38%;">Akun</th>
                        <th style="width: 20%;">Total Lead</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminRanking as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['account'] }}</td>
                            <td class="text-right">{{ $item['total'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">Belum ada data ranking admin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">Lampiran Data Konsultasi</div>
        <div class="section-note">Menampilkan 25 data terbaru. Detail lengkap tetap tersedia pada file Excel.</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 11%;">ID</th>
                    <th style="width: 16%;">Klien</th>
                    <th style="width: 11%;">Telepon</th>
                    <th style="width: 12%;">Akun</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 12%;">Kebutuhan</th>
                    <th style="width: 10%;">Kota</th>
                    <th style="width: 8%;">Tanggal</th>
                    <th style="width: 10%;">Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rawRows->take(25) as $row)
                    <tr>
                        <td>{{ $row['consultation_id'] }}</td>
                        <td>{{ $row['client_name'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td>{{ $row['account'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['need'] }}</td>
                        <td>{{ $row['city'] }}</td>
                        <td class="text-center">{{ $row['consultation_date'] }}</td>
                        <td class="text-center">{{ $row['updated_at'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">Belum ada data pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
