<?php

use App\Models\User;

$users = User::where('email', 'like', '%@atelier.com')->get();

foreach ($users as $user) {
    $oldEmail = $user->email;
    $user->email = str_replace('@atelier.com', '@ekonsul.com', $user->email);
    $user->save();
    echo "✅ {$oldEmail} → {$user->email}\n";
}

echo "\n🎉 Total {$users->count()} akun email berhasil diupdate ke @ekonsul.com\n";
