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
            $table->text('staff_officer_rejection_reason')->nullable()->after('rejection_reason');
            $table->timestamp('finalized_at')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aper_forms', function (Blueprint $table) {
            $table->dropColumn(['staff_officer_rejection_reason', 'finalized_at']);
        });
    }
};
