<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->date('report_date');
            $table->timestamps();

            // Seorang admin hanya bisa melaporkan kehadiran satu kali per hari per akun
            $table->unique(['user_id', 'account_id', 'report_date'], 'user_account_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_attendances');
    }
};
