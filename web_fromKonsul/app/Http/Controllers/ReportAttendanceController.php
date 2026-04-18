<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use App\Models\ReportAttendance;
use Carbon\Carbon;
use App\Models\User;

class ReportAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Hanya Super Admin yang bisa mengakses monitoring
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $dateParam = $request->get('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($dateParam);

        $dateStr = $date->format('Y-m-d');

        $adminAttendances = User::where('role', UserRole::Admin)
            ->with(['account', 'reportAttendances' => fn($q) => $q->where('report_date', $dateStr)])
            ->get()
            ->map(function($admin) {
                $attendance = $admin->reportAttendances->first();
                return (object) [
                    'admin' => $admin,
                    'account' => $admin->account,
                    'has_reported' => $attendance !== null,
                    'reported_at' => $attendance?->created_at,
                    'report_category' => $attendance?->report_category,
                ];
            });

        return view('report-attendances.index', compact('adminAttendances', 'date'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Hanya admin yang bisa absen
        if (!$user->isAdmin()) {
            return back()->with('error', 'Hanya admin yang dapat melakukan absensi report.');
        }

        $request->validate([
            'report_category' => 'required|in:ada_wa,nol_wa,libur_susulan',
        ], [
            'report_category.required' => 'Pilih kategori laporan absen Anda.',
            'report_category.in' => 'Kategori yang dipilih tidak valid.'
        ]);

        $today = Carbon::today();

        // Cek apakah sudah absen hari ini
        $exists = ReportAttendance::where('user_id', $user->id)
            ->where('report_date', $today)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah melakukan absensi hari ini.');
        }

        ReportAttendance::create([
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'report_date' => $today,
            'report_category' => $request->report_category,
        ]);

        return back()->with('success', 'Berhasil melakukan absensi report harian!');
    }
}
