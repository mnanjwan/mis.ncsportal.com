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
        Schema::table('duty_rosters', function (Blueprint $table) {
            $table->foreignId('oic_officer_id')->nullable()->after('approved_by')->constrained('officers')->nullOnDelete();
            $table->foreignId('second_in_command_officer_id')->nullable()->after('oic_officer_id')->constrained('officers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duty_rosters', function (Blueprint $table) {
            $table->dropForeign(['oic_officer_id']);
            $table->dropForeign(['second_in_command_officer_id']);
            $table->dropColumn(['oic_officer_id', 'second_in_command_officer_id']);
        });
    }
};
