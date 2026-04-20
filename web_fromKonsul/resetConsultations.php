<?php

use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

$reminders = DB::table('reminders')->count();
DB::table('reminders')->truncate();
echo "🗑️  Reminders: {$reminders} data dihapus\n";

$notes = DB::table('consultation_notes')->count();
DB::table('consultation_notes')->truncate();
echo "🗑️  Catatan Follow-Up: {$notes} data dihapus\n";

$consultations = DB::table('consultations')->count();
DB::table('consultations')->truncate();
echo "🗑️  Konsultasi: {$consultations} data dihapus\n";

// Hapus audit logs terkait jika ada
if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
    $audits = DB::table('audit_logs')->count();
    DB::table('audit_logs')->truncate();
    echo "🗑️  Audit Logs: {$audits} data dihapus\n";
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\n✅ Semua data konsultasi berhasil di-reset! Database bersih dari awal.\n";
