<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->boolean('resumption_reminder_sent')->default(false)->after('expiry_date');
            $table->boolean('resumption_day_alert_sent')->default(false)->after('resumption_reminder_sent');
        });

        Schema::table('pass_applications', function (Blueprint $table) {
            $table->boolean('resumption_reminder_sent')->default(false)->after('expiry_date');
            $table->boolean('resumption_day_alert_sent')->default(false)->after('resumption_reminder_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn(['resumption_reminder_sent', 'resumption_day_alert_sent']);
        });

        Schema::table('pass_applications', function (Blueprint $table) {
            $table->dropColumn(['resumption_reminder_sent', 'resumption_day_alert_sent']);
        });
    }
};
