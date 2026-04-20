<?php

use App\Models\Account;

/**
 * Update description/tagline akun berdasarkan kelompok grup.
 */

$groups = [
    'PUTRA CORPORATION' => [
        'HOME INTERIOR BANDUNG',
        'INTERHOUSE',
        'ZODIAC INTERIOR',
        'AKBAR INTERIOR',
        'PARTNER INTERIOR',
        'ELVAN INTERIOR',
        'MEWAH INTERIOR',
        'MEDIAN INTERIOR',
        'ARGO INTERIOR',
        'SAVOY INTERIOR',
        'FURNITURE CIMAHI',
        'DEKOR INTERIOR',
        'NISCALA FURNITURE',
        'INTERIOR CUSTOM',
        'INTERIOR BANDUNG',
        'MODERN INTERIOR',
        'BROTO INTERIOR',
        'KITCHENSET SOLUTION BANDUNG',
        'GIBRAN INTERIOR',
        'HOME SAVOY INTERIOR',
        'LAVENTIA',
    ],
    'FULLHOME GROUP' => [
        'HOME PUTRA INTERIOR',
        'PUTRA INTERIOR',
        'FULLHOME ID',
        'KAMARSET ID',
        'PUTRO INTERIOR',
        'PUSAT INTERIOR',
    ],
    'INSIP GRUP' => [
        'PUTRA MOULDING',
        'HEYA INTERIOR',
        'KURNIA INTERIOR',
    ],
    'KOLABORASI' => [
        'RAYSA INTERIOR',
        'KEJORA INTERIOR',
    ],
    'PORTO GROUP' => [
        'ANEKA INTERIOR',
        'RADEA INTERIOR',
    ],
    'SAGARA GROUP' => [
        'ROMO INTERIOR',
        'CENDANA',
    ],
];

$updated = 0;

foreach ($groups as $groupName => $accountNames) {
    foreach ($accountNames as $name) {
        $account = Account::where('name', $name)->first();
        if ($account) {
            $account->description = $groupName;
            $account->save();
            $idFormatted = str_pad($account->id, 2, '0', STR_PAD_LEFT);
            echo "✅ #{$idFormatted} {$name} → {$groupName}\n";
            $updated++;
        } else {
            echo "⚠️  '{$name}' tidak ditemukan di database, dilewati.\n";
        }
    }
    echo "────────────────────────────────────────\n";
}

// Akun yang tidak masuk grup manapun → set deskripsi "INDEPENDEN"
$ungrouped = Account::whereNotIn('description', array_keys($groups))
    ->orWhereNull('description')
    ->orWhere('description', 'like', 'Kode:%')
    ->get();

foreach ($ungrouped as $acc) {
    $acc->description = 'INDEPENDEN';
    $acc->save();
    $idFormatted = str_pad($acc->id, 2, '0', STR_PAD_LEFT);
    echo "📦 #{$idFormatted} {$acc->name} → INDEPENDEN\n";
    $updated++;
}

echo "\n🎉 Selesai! {$updated} akun berhasil diupdate kategori grupnya.\n";
