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
        Schema::table('pharmacy_procurement_items', function (Blueprint $table) {
            // Make drug_id nullable (drug is linked when received/approved)
            $table->foreignId('pharmacy_drug_id')->nullable()->change();
            
            // Add direct fields for drug name and unit
            $table->string('drug_name')->nullable()->after('pharmacy_procurement_id');
            $table->string('unit_of_measure')->default('units')->after('drug_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_procurement_items', function (Blueprint $table) {
            $table->dropColumn(['drug_name', 'unit_of_measure']);
            $table->foreignId('pharmacy_drug_id')->nullable(false)->change();
        });
    }
};
