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
        Schema::table('aper_timelines', function (Blueprint $table) {
            // Change date columns to datetime to support time
            $table->datetime('start_date')->change();
            $table->datetime('end_date')->change();
            $table->datetime('extension_end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aper_timelines', function (Blueprint $table) {
            // Revert back to date columns
            $table->date('start_date')->change();
            $table->date('end_date')->change();
            $table->date('extension_end_date')->nullable()->change();
        });
    }
};
