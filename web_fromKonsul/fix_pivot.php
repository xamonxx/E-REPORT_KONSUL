<?php
use App\Models\User;
use Illuminate\Support\Facades\DB;

$users = User::whereNotNull('account_id')->get();
foreach ($users as $user) {
    DB::table('account_user')->updateOrInsert(
        ['user_id' => $user->id, 'account_id' => $user->account_id],
        ['created_at' => now(), 'updated_at' => now()]
    );
}

echo "Pivot table populated for M:N relationships!\n";
