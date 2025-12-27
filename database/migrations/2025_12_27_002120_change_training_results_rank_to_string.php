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
        Schema::table('training_results', function (Blueprint $table) {
            // Change rank column from integer to string to store substantive rank (e.g., 'CSC', 'PD')
            $table->string('rank', 50)->nullable()->change()->comment('Substantive rank of the officer (e.g., CSC, PD)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_results', function (Blueprint $table) {
            // Revert back to integer (though this may cause data loss if string values exist)
            $table->integer('rank')->nullable()->change()->comment('Rank based on performance (1 = highest, 2 = second, etc.)');
        });
    }
};
