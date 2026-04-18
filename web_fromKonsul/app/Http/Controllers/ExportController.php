<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $query = Consultation::with(['account', 'needsCategory', 'statusCategory', 'creator']);

        $query->forUser($user);

        if ($user->isSuperAdmin() && $request->filled('account')) {
            $query->where('account_id', $request->account);
        }

        if ($request->filled('month')) {
            $query->whereMonth('consultation_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('consultation_date', $request->year);
        }

        $filename = 'Data_Leads_' . now()->format('Ymd_His') . '.csv';

        // Stream CSV directly — avoids loading all records into memory
        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fputs($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputs($handle, "sep=,\n");

            // Header row
            fputcsv($handle, [
                'ID Konsultasi', 'Nama Klien', 'No. Telepon', 'Provinsi', 'Kota',
                'Akun', 'Jenis Kebutuhan', 'Status', 'Catatan',
                'Tanggal Konsultasi', 'Dibuat Oleh', 'Tanggal Update'
            ]);

            // Stream rows via lazy() — processes one row at a time, no memory spike
            foreach ($query->orderBy('consultation_date', 'desc')->lazy(500) as $c) {
                $phone = $c->phone ? "'" . $c->phone : '';

                fputcsv($handle, [
                    $c->consultation_id,
                    $c->client_name,
                    $phone,
                    $c->province,
                    $c->city,
                    $c->account?->name,
                    $c->needsCategory?->name,
                    $c->statusCategory?->name,
                    $c->getAttribute('notes'),
                    $c->consultation_date?->format('d/m/Y'),
                    $c->creator?->name,
                    $c->updated_at?->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Pragma' => 'public',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
