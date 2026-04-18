<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_categories', function (Blueprint $table) {
            $table->string('css_class')->nullable()->after('color');
        });

        // Isi css_class untuk data yang sudah ada berdasarkan nama
        $mapping = [
            'Hanya Tanya Tanya'  => 'chip-hanya-tanya',
            'Masuk Survey'       => 'chip-masuk-survey',
            'Kendala Anggaran'   => 'chip-kendala-anggaran',
            'Tidak Ada Respon'   => 'chip-tidak-ada-respon',
            'Selesai/Deal'       => 'chip-selesai-deal',
        ];

        foreach ($mapping as $name => $cssClass) {
            \DB::table('status_categories')
                ->where('name', $name)
                ->update(['css_class' => $cssClass]);
        }
    }

    public function down(): void
    {
        Schema::table('status_categories', function (Blueprint $table) {
            $table->dropColumn('css_class');
        });
    }
};
