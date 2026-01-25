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
        Schema::create('pharmacy_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_drug_id')->constrained('pharmacy_drugs')->onDelete('cascade');
            $table->string('movement_type'); // PROCUREMENT_RECEIPT, REQUISITION_ISSUE, ADJUSTMENT, DISPENSED
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable(); // PharmacyProcurement, PharmacyRequisition
            $table->string('location_type'); // CENTRAL_STORE, COMMAND_PHARMACY
            $table->foreignId('command_id')->nullable()->constrained('commands')->onDelete('cascade');
            $table->integer('quantity'); // positive for additions, negative for subtractions
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('pharmacy_drug_id');
            $table->index('movement_type');
            $table->index('location_type');
            $table->index('command_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_stock_movements');
    }
};
