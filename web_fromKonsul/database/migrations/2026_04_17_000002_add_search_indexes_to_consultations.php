<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Index untuk mempercepat pencarian (Fix #5)
            // consultation_id sudah unique (otomatis indexed), jadi tidak perlu ditambahkan
            $table->index('client_name');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex(['client_name']);
            $table->dropIndex(['phone']);
        });
    }
};
