<?php

use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

$filename = 'Export_Data_Login_Admin.csv';
$filepath = public_path($filename);
$file = fopen($filepath, 'w');

// Tambahkan BOM untuk UTF-8 agar Excel membacanya dengan benar
fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

// Tulis header
fputcsv($file, ['No', 'Grup / Kategori', 'Nama Akun', 'Kode ID Akun', 'Nama Admin', 'Email Login', 'Password Default', 'Role']);

// Ambil semua user beserta akunnya
$users = User::with('account')
    ->orderBy('role', 'desc') // Super Admin di atas
    ->get();

$no = 1;
foreach ($users as $user) {
    if ($user->isSuperAdmin()) {
        fputcsv($file, [
            $no++,
            '-',
            'SEMUA AKUN / PUSAT',
            '-',
            $user->name,
            $user->email,
            'Silakan cek di database', 
            'Super Admin'
        ]);
        continue;
    }

    $account = $user->account;
    $accountName = $account ? $account->name : '-';
    $accountIdRaw = $account ? $account->id : '-';
    $accountId = $account ? str_pad($account->id, 2, '0', STR_PAD_LEFT) : '-';
    $group = $account ? $account->description : '-';

    fputcsv($file, [
        $no++,
        $group,
        $accountName,
        $accountId,
        $user->name,
        $user->email,
        '123321', // Sesuai default di seeder
        'Admin'
    ]);
}

// Tambahkan juga akun yang BELUM punya admin agar lengkap informasinya
$accountsWithoutAdmin = Account::doesntHave('users')->orderBy('id')->get();

foreach ($accountsWithoutAdmin as $account) {
    fputcsv($file, [
        $no++,
        $account->description ?? '-',
        $account->name,
        str_pad($account->id, 2, '0', STR_PAD_LEFT),
        '(Belum Ada Admin)',
        '-',
        '-',
        '-'
    ]);
}

fclose($file);

echo "✅ File CSV berhasil dibuat!\n";
echo "Lokasi: {$filepath}\n";
echo "URL Download: http://localhost:8000/{$filename}\n";
