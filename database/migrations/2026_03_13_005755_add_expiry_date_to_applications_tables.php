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
            $table->date('expiry_date')->nullable()->after('end_date')->comment('The calculated date when leave ends, skipping weekends and holidays');
        });

        Schema::table('pass_applications', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('end_date')->comment('The calculated date when pass ends, skipping weekends and holidays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });

        Schema::table('pass_applications', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });
    }
};
