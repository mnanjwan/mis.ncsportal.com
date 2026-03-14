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
        Schema::table('pharmacy_requisitions', function (Blueprint $table) {
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_requisitions', function (Blueprint $table) {
            $table->dropForeign(['dispensed_by']);
            $table->dropColumn('dispensed_by');
        });
    }
};
