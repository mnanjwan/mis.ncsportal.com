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
        Schema::table('aper_forms', function (Blueprint $table) {
            // Add missing field from Section 6 (Job Description)
            $table->string('served_under_supervisor', 255)->nullable()->after('schedule_duty_to');
            
            // Add missing field from Section 9 (Assessment of Performance)
            $table->text('other_comments')->nullable()->after('duties_agreement_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aper_forms', function (Blueprint $table) {
            $table->dropColumn(['served_under_supervisor', 'other_comments']);
        });
    }
};
