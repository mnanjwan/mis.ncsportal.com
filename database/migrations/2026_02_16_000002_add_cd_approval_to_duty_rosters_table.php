<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('duty_rosters', 'cd_approved_at')) {
            Schema::table('duty_rosters', function (Blueprint $table) {
                $table->timestamp('cd_approved_at')->nullable()->after('approved_at');
            });
        }
        if (!Schema::hasColumn('duty_rosters', 'cd_approved_by')) {
            Schema::table('duty_rosters', function (Blueprint $table) {
                $table->unsignedBigInteger('cd_approved_by')->nullable()->after('cd_approved_at');
            });
        }
        try {
            Schema::table('duty_rosters', function (Blueprint $table) {
                $table->foreign('cd_approved_by')->references('id')->on('officers')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // FK may already exist or fail on some MySQL configs; column is sufficient
        }
    }

    public function down(): void
    {
        try {
            Schema::table('duty_rosters', function (Blueprint $table) {
                $table->dropForeign(['cd_approved_by']);
            });
        } catch (\Throwable $e) {
            // FK may not exist
        }
        Schema::table('duty_rosters', function (Blueprint $table) {
            $table->dropColumn(['cd_approved_at', 'cd_approved_by']);
        });
    }
};
