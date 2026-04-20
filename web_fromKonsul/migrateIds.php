<?php

/**
 * Script untuk migrasi ID Konsultasi lama ke format baru: AA.YYMM.NNN
 * 
 * Jalankan via: php artisan tinker migrateIds.php
 */

use App\Models\Consultation;
use Illuminate\Support\Facades\DB;

$accounts = DB::table('accounts')->orderBy('id')->get();

$totalUpdated = 0;

foreach ($accounts as $account) {
    $accountPadded = str_pad($account->id, 2, '0', STR_PAD_LEFT);
    
    // Ambil semua konsultasi milik akun ini, urutkan berdasarkan tanggal dibuat
    $consultations = Consultation::where('account_id', $account->id)
        ->orderBy('created_at')
        ->get();
    
    // Group berdasarkan bulan (YYMM)
    $grouped = $consultations->groupBy(function ($c) {
        return $c->created_at->format('ym');
    });
    
    foreach ($grouped as $yearMonth => $items) {
        $seq = 1;
        foreach ($items as $consultation) {
            $newId = $accountPadded . '.' . $yearMonth . '.' . str_pad($seq, 3, '0', STR_PAD_LEFT);
            
            // Hanya update jika ID berubah
            if ($consultation->consultation_id !== $newId) {
                DB::table('consultations')
                    ->where('id', $consultation->id)
                    ->update(['consultation_id' => $newId]);
                $totalUpdated++;
            }
            $seq++;
        }
    }
    
    echo "✅ Akun #{$account->id} ({$account->name}): " . $consultations->count() . " data diproses\n";
}

echo "\n🎉 Selesai! Total {$totalUpdated} ID konsultasi diperbarui ke format baru.\n";
