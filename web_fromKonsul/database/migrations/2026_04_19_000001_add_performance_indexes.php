<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // consultations — soft delete, date filters, dedup check
        Schema::table('consultations', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('consultation_date');
            $table->index('created_at');
            $table->index(['phone', 'account_id']);
        });

        // consultation_notes — notification count queries
        Schema::table('consultation_notes', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index(['is_read', 'user_id']);
        });

        // reminders — notification queries
        Schema::table('reminders', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index(['user_id', 'is_read', 'remind_at']);
        });

        // report_attendances — date lookups
        Schema::table('report_attendances', function (Blueprint $table) {
            $table->index('report_date');
        });

        // status_categories — sort & name lookups
        Schema::table('status_categories', function (Blueprint $table) {
            $table->index('sort_order');
            $table->index('name');
        });

        // users — role filtering
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['consultation_date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['phone', 'account_id']);
        });

        Schema::table('consultation_notes', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['is_read', 'user_id']);
        });

        Schema::table('reminders', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['user_id', 'is_read', 'remind_at']);
        });

        Schema::table('report_attendances', function (Blueprint $table) {
            $table->dropIndex(['report_date']);
        });

        Schema::table('status_categories', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
