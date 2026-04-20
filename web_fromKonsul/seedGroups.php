<?php

use App\Enums\UserRole;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * Data Lengkap Akun & Admin
 * Format: [Nama Akun, Kode ID, Nama Admin (null = belum ada)]
 * Urutan = urutan ID database (1-84)
 */
$accounts = [
    // NO 1-10
    ['HOME INTERIOR BANDUNG', '01', 'HASAN'],
    ['INTERIOR BANDUNG', '02', 'DIKI'],
    ['INTERHOUSE', '03', 'ANDIKA'],
    ['ARGO INTERIOR', '04', 'ANGBILAL'],
    ['GIBRAN INTERIOR', '05', 'NENG SRI'],
    ['MEWAH INTERIOR', '06', 'LISA'],
    ['LAVENTIA', '07', 'ANO'],
    ['KITCHENSET BANDUNG BARAT 93', '08', 'WILDAN'],
    ['SAVANA INTERIOR', '09', 'AMIR'],
    ['NISCALA FURNITURE', '10', 'RIVALDI'],

    // NO 11-20
    ['KURNIA INTERIOR', '11', null],
    ['PUTRO INTERIOR', '12', null],
    ['MEDIAN INTERIOR', '13', 'NENG SRI'],
    ['HOME SAVOY INTERIOR', '14', 'ZAMZAM'],
    ['PARTNER INTERIOR', '15', 'YANWAR'],
    ['DEKOR INTERIOR', '16', 'DIAN'],
    ['MALAVIA INTERIOR', '17', null],
    ['PORTU INTERIOR', '18', 'HASRI'],
    ['ALTAR INTERIOR', '19', null],
    ['ZODIAC INTERIOR', '20', 'BILAL'],

    // NO 21-30
    ['SAVOY INTERIOR', '21', 'ZAMZAM'],
    ['PALEM INTERIOR', '22', 'DIAN'],
    ['RAYSA INTERIOR', '23', 'JANUAR'],
    ['ANEKA INTERIOR', '24', 'RAMDAN'],
    ['ZONA INTERIOR', '25', null],
    ['PESONA INTERIOR CUSTOM', '26', null],
    ['FURNITURE CUSTOM', '27', 'AKBAR'],
    ['DURAGAN INTERIOR', '28', null],
    ['ELVAN INTERIOR', '29', 'YANWAR'],
    ['AKBAR INTERIOR', '30', 'YONAS'],

    // NO 31-40
    ['PUSAT INTERIOR', '31', 'RIFKI'],
    ['PARADE INTERIOR', '32', 'WILDAN'],
    ['FURNITURE CIMAHI', '33', 'ANO'],
    ['GARIS INTERIOR', '34', 'AKBAR'],
    ['MARWAH FURNITURE', '35', 'UBAY'],
    ['GALERY HOME PARTNER', '36', null],
    ['ROMO INTERIOR', '37', null],
    ['INTERIOR CUSTOM', '38', 'DIAN'],
    ['PUTRA INTERIOR', '39', 'YASID'],
    ['CENDANA', '40', 'AGUNG'],

    // NO 41-50
    ['AKROS INTERIOR', '41', null],
    ['KITCHENSET SOLUTION BANDUNG', '42', 'NENG SRI'],
    ['RUMA FURNITURE', '43', 'DIAN'],
    ['HOME PUTRA INTERIOR', '44', 'FIKRI'],
    ['MODERN INTERIOR', '45', null],
    ['SOLUTION INTERIOR', '46', null],
    ['JOYO INTERIOR', '47', 'ANO'],
    ['RADEA INTERIOR', '48', 'IRWAN'],
    ['KANA INTERIOR', '49', null],
    ['NAZELA INTERIOR', '50', null],

    // NO 51-60
    ['PANDAWA INTERIOR', '51', null],
    ['CASA INTERIOR', '52', null],
    ['PRANATA FURNITURE', '53', 'AKBAR'],
    ['KITCHEN SET KOTA BANDUNG', '54', null],
    ['ARYO INTERIOR', '55', null],
    ['RAYA INTERIOR', '56', null],
    ['RUANG RENOVASI', '57', 'UBAY'],
    ['ALVANA', '58', null],
    ['NULAN', '59', null],
    ['RESELLER / DOOR TO DOOR', 'RSL', null],

    // NO 61-70 (U-Series)
    ['GEMILANG INTERIOR', 'U01', 'ANO'],
    ['JENAKA INTERIOR', 'U02', 'DIAN'],
    ['DATTA INTERIOR', 'U03', null],
    ['MANDIRI INTERIOR', 'U04', null],
    ['ARTHA INTERIOR', 'U05', null],
    ['KAHIJI INTERIOR', 'K06', null],
    ['KUMBO INTERIOR', 'K07', null],
    ['BAKTI INTERIOR', 'U08', null],
    ['NAMA INTERIOR', 'U09', null],
    ['GRAHA INTERIOR', 'U10', null],

    // NO 71-84
    ['AULA INTERIOR', 'U11', null],
    ['GLOBAL INTERIOR', 'U12', 'ANO'],
    ['CENDANA INTERIOR', 'U13', null],
    ['GAYA INTERIOR', 'U14', 'AKBAR'],
    ['BROTO INTERIOR', 'U15', 'DIAN'],
    ['ELVAN FURNITURE', 'U16', 'YANWAR'],
    ['FULLHOME ID', 'U17', 'ANO'],
    ['CEMA INTERIOR', 'U18', null],
    ['HEYA INTERIOR', 'U19', 'DIAN'],
    ['HOME SAVOY', 'U20', 'ZAMZAM'],
    ['KAMARSET ID', 'U21', null],
    ['KEJORA INTERIOR', 'U22', 'ANO'],
    ['PUTRA MOULDING', 'U23', 'ANGGA'],
    ['WOODY HOUSE', 'U24', null],
];

// ── Clean existing data (kecuali SuperAdmin) ──────────────
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Account::truncate();
DB::table('account_user')->truncate();
User::where('role', '!=', UserRole::SuperAdmin)->delete();
// Reset auto-increment
DB::statement('ALTER TABLE accounts AUTO_INCREMENT = 1;');
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

$password = Hash::make('123321');
$totalAccounts = 0;
$totalAdmins = 0;

foreach ($accounts as $index => $data) {
    [$accountName, $code, $adminName] = $data;
    $dbId = $index + 1; // Sequential ID: 1, 2, 3, ...

    // Create the Account
    $account = Account::create([
        'name' => $accountName,
        'description' => "Kode: {$code}",
        'target_leads' => 50,
    ]);
    $totalAccounts++;

    // Pastikan ID sesuai urutan
    if ($account->id !== $dbId) {
        DB::table('accounts')->where('id', $account->id)->update(['id' => $dbId]);
        $account->id = $dbId;
    }

    if ($adminName) {
        // Generate email dari nama akun (huruf kecil, tanpa spesial karakter)
        $emailUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $accountName));
        $email = $emailUsername . '@ekonsul.com';

        // Cek duplikat email
        $suffix = 1;
        $baseEmail = $email;
        while (User::where('email', $email)->exists()) {
            $email = str_replace('@', $suffix . '@', $baseEmail);
            $suffix++;
        }

        User::create([
            'name' => $adminName,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Admin,
            'account_id' => $account->id,
        ]);
        $totalAdmins++;

        $idFormatted = str_pad($account->id, 2, '0', STR_PAD_LEFT);
        echo "✅ #{$idFormatted} {$accountName} → Admin: {$adminName} ({$email})\n";
    } else {
        $idFormatted = str_pad($account->id, 2, '0', STR_PAD_LEFT);
        echo "📦 #{$idFormatted} {$accountName} → (Belum ada admin)\n";
    }
}

echo "\n══════════════════════════════════════════\n";
echo "🎉 Selesai! {$totalAccounts} akun & {$totalAdmins} admin berhasil dibuat.\n";
echo "💡 Password default semua admin: 123321\n";
echo "══════════════════════════════════════════\n";
