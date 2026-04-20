<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\NeedsCategory;
use App\Models\StatusCategory;
use App\Models\Account;
use App\Http\Requests\ConsultationRequest;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Consultation::with(['account', 'needsCategory', 'statusCategory', 'creator']);

        $query->forUser($user);

        // Filters
        if ($request->filled('status')) {
            $query->where('status_category_id', $request->status);
        }
        if ($request->filled('account')) {
            if ($user->isSuperAdmin()) {
                $query->where('account_id', $request->account);
            } elseif ($user->account_id == $request->account) {
                $query->where('account_id', $request->account);
            }
        }
        if ($request->filled('start_date')) {
            $query->whereDate('consultation_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('consultation_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('consultation_id', 'like', "%{$search}%");
            });
        }

        $consultations = $query->latest()->paginate(15)->withQueryString();

        $statuses = StatusCategory::orderBy('sort_order')->get();
        $accounts = $user->isSuperAdmin() ? Account::orderBy('name')->get() : ($user->account ? collect([$user->account]) : collect([]));

        // Data needed for Create Consultation Modal
        $previewAccountId = $user->isAdmin() ? $user->account_id : ($accounts->first()->id ?? 1);
        $newId = Consultation::generateConsultationId($previewAccountId);
        $categories = NeedsCategory::orderBy('name')->get();
        $provinces = config('wilayah.provinces');

        return view('consultations.index', compact('consultations', 'statuses', 'accounts', 'newId', 'categories', 'provinces'));
    }

    public function create()
    {
        $user = auth()->user();
        $accounts = $user->isSuperAdmin() ? Account::orderBy('name')->get() : ($user->account ? collect([$user->account]) : collect([]));
        
        // Dapatkan default account ID untuk preview ID Consultation
        $previewAccountId = $user->isAdmin() ? $user->account_id : ($accounts->first()->id ?? 1);
        
        $newId = Consultation::generateConsultationId($previewAccountId);
        $categories = NeedsCategory::orderBy('name')->get();
        $statuses = StatusCategory::orderBy('sort_order')->get();

        // Provinsi dari config — satu sumber data (Fix #1a)
        $provinces = config('wilayah.provinces');

        return view('consultations.create', compact('newId', 'categories', 'statuses', 'accounts', 'provinces'));
    }

    public function store(ConsultationRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        // Security: admin can only create for their own account
        if ($user->isAdmin()) {
            if ($user->account_id != $validated['account_id']) {
                abort(403);
            }
        }

        // Deduplication Check
        $exists = Consultation::where('phone', $validated['phone'])
            ->where('account_id', $validated['account_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['phone' => 'Klien dengan nomor telepon ini sudah ada di akun/cabang Anda.'])->withInput();
        }

        $validated['consultation_id'] = Consultation::generateConsultationId($validated['account_id']);
        $validated['created_by'] = $user->id;
        $validated['consultation_date'] = $validated['consultation_date'] ?? now()->toDateString();

        Consultation::create($validated);

        return redirect()->route('consultations.index')
            ->with('success', 'Konsultasi baru berhasil ditambahkan!');
    }

    public function show(Consultation $consultation)
    {
        $this->authorize('view', $consultation);

        $user = auth()->user();
        $consultation->load(['account', 'needsCategory', 'statusCategory', 'timelineNotes.user', 'reminders.user']);

        // Mark unread notes from others as read
        $updated = $consultation->timelineNotes()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated) {
            \Illuminate\Support\Facades\Cache::forget("api_notif_{$user->id}");
        }

        return view('consultations.show', compact('consultation'));
    }

    public function edit(Consultation $consultation)
    {
        $this->authorize('update', $consultation);

        $user = auth()->user();
        $categories = NeedsCategory::orderBy('name')->get();
        $statuses = StatusCategory::orderBy('sort_order')->get();
        $accounts = $user->isSuperAdmin() ? Account::orderBy('name')->get() : ($user->account ? collect([$user->account]) : collect([]));

        // Provinsi dari config — satu sumber data (Fix #1a)
        $provinces = config('wilayah.provinces');

        return view('consultations.edit', compact('consultation', 'categories', 'statuses', 'accounts', 'provinces'));
    }

    public function update(ConsultationRequest $request, Consultation $consultation)
    {
        $this->authorize('update', $consultation);

        $user = auth()->user();
        $validated = $request->validated();

        if ($user->isAdmin()) {
            if ($user->account_id != $validated['account_id']) {
                abort(403);
            }
        }
        $exists = Consultation::where('phone', $validated['phone'])
            ->where('account_id', $validated['account_id'])
            ->where('id', '!=', $consultation->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['phone' => 'Klien dengan nomor telepon ini sudah ada di akun/cabang Anda.'])->withInput();
        }

        $consultation->update($validated);

        return redirect()->route('consultations.index')
            ->with('success', 'Data konsultasi berhasil diperbarui!');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle, 1000, ','); // skip header row

        $user = auth()->user();
        $successCount = 0;
        $duplicateCount = 0;
        $errorRows = [];
        $rowNumber = 1;

        $defaultStatus = StatusCategory::first();
        $defaultCategory = NeedsCategory::first();

        if (!$defaultStatus || !$defaultCategory) {
            fclose($handle);
            return back()->withErrors([
                'csv_file' => 'Gagal import: Master data Status atau Kategori Kebutuhan belum tersedia. Silakan tambahkan di Master Data terlebih dahulu.',
            ]);
        }

        $validAccountIds = Account::pluck('id')->toArray();

        // Pre-load existing phone+account pairs to avoid 1 query per row
        $existingPairs = Consultation::select('phone', 'account_id')
            ->pluck('account_id', 'phone')
            ->mapToGroups(fn($accountId, $phone) => [$phone => $accountId])
            ->toArray();

        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;

                $parsed = $this->parseCsvRow($row, $rowNumber, $user, $validAccountIds);

                if (is_string($parsed)) {
                    $errorRows[] = $parsed;
                    continue;
                }

                // Cek duplikat via pre-loaded set (no DB query per row)
                $phone = $parsed['phone'];
                $accountId = $parsed['account_id'];
                if (isset($existingPairs[$phone]) && in_array($accountId, $existingPairs[$phone])) {
                    $duplicateCount++;
                    continue;
                }

                Consultation::create([
                    'consultation_id'    => Consultation::generateConsultationId($accountId),
                    'client_name'        => $parsed['client_name'],
                    'phone'              => $phone,
                    'account_id'         => $accountId,
                    'needs_category_id'  => $defaultCategory->id,
                    'status_category_id' => $defaultStatus->id,
                    'created_by'         => $user->id,
                    'consultation_date'  => now(),
                ]);

                // Track newly inserted pair to catch intra-file duplicates
                $existingPairs[$phone][] = $accountId;
                $successCount++;
            }
        } catch (\Exception $e) {
            fclose($handle);
            return back()->withErrors([
                'csv_file' => 'Terjadi kesalahan saat import: ' . $e->getMessage(),
            ]);
        }

        fclose($handle);

        return back()->with('success', $this->buildImportReport($successCount, $duplicateCount, $errorRows));
    }

    /**
     * Parse & sanitize satu baris CSV.
     *
     * @return array{client_name: string, phone: string, account_id: int}|string Error message jika gagal
     */
    private function parseCsvRow(array $row, int $rowNumber, $user, array $validAccountIds): array|string
    {
        if (count($row) < 2) {
            return "Baris {$rowNumber}: kolom tidak lengkap (minimal 2 kolom).";
        }

        $clientName = preg_replace('/^[=+\-\@\t\r\n]/', '', trim($row[0] ?? ''));
        $phone      = preg_replace('/^[=+\-\@\t\r\n]/', '', trim($row[1] ?? ''));

        if (empty($clientName) || empty($phone)) {
            return "Baris {$rowNumber}: nama klien atau telepon kosong.";
        }

        // Tentukan account_id
        if ($user->isAdmin()) {
            $accountId = $user->account_id;
        } else {
            $rawAccountId = isset($row[2]) ? trim($row[2]) : null;
            if ($rawAccountId && !in_array($rawAccountId, $validAccountIds)) {
                return "Baris {$rowNumber}: Akun ID '{$rawAccountId}' tidak ditemukan di database.";
            }
            $accountId = $rawAccountId ?: ($validAccountIds[0] ?? null);

            if (!$accountId) {
                return "Baris {$rowNumber}: Tidak ada akun tersedia.";
            }
        }

        return ['client_name' => $clientName, 'phone' => $phone, 'account_id' => $accountId];
    }

    /**
     * Bangun pesan laporan hasil import CSV.
     */
    private function buildImportReport(int $successCount, int $duplicateCount, array $errorRows): string
    {
        $messages = ["{$successCount} data lead berhasil diimpor."];

        if ($duplicateCount > 0) {
            $messages[] = "{$duplicateCount} data dilewati (duplikat).";
        }
        if (count($errorRows) > 0) {
            $messages[] = count($errorRows) . " baris error:";
            $messages = array_merge($messages, array_slice($errorRows, 0, 10));
        }

        return implode("\n", $messages);
    }

    public function downloadTemplate()
    {
        $fileName = 'template_import_leads.csv';
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['Nama Klien', 'No Telepon', 'ID Akun (Kosongkan jika Admin)'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Budi Santoso', '081234567890', '1']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Consultation $consultation)
    {
        $this->authorize('delete', $consultation);

        $consultation->delete();

        return redirect()->route('consultations.index')
            ->with('success', 'Data konsultasi berhasil dihapus!');
    }

    /**
     * API: Preview consultation ID based on selected account.
     */
    public function previewId(Request $request)
    {
        $accountId = $request->input('account_id', auth()->user()->account_id ?? 1);
        $previewId = Consultation::generateConsultationId($accountId);

        return response()->json(['id' => $previewId]);
    }
}
