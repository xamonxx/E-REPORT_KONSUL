<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_attendances', function (Blueprint $table) {
            $table->string('report_category')->nullable()->after('report_date');
        });
    }

    public function down(): void
    {
        Schema::table('report_attendances', function (Blueprint $table) {
            $table->dropColumn('report_category');
        });
    }
};
