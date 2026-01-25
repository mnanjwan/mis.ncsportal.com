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
        Schema::create('pharmacy_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_requisition_id')->constrained('pharmacy_requisitions')->onDelete('cascade');
            $table->foreignId('pharmacy_drug_id')->constrained('pharmacy_drugs')->onDelete('cascade');
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_issued')->default(0);
            $table->timestamps();

            $table->index('pharmacy_requisition_id');
            $table->index('pharmacy_drug_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_requisition_items');
    }
};
