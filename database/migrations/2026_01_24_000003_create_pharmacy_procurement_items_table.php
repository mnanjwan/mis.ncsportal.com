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
        Schema::create('pharmacy_procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_procurement_id')->constrained('pharmacy_procurements')->onDelete('cascade');
            $table->foreignId('pharmacy_drug_id')->constrained('pharmacy_drugs')->onDelete('cascade');
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_received')->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->timestamps();

            $table->index('pharmacy_procurement_id');
            $table->index('pharmacy_drug_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_procurement_items');
    }
};
