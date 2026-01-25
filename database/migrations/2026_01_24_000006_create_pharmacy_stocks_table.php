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
        Schema::create('pharmacy_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_drug_id')->constrained('pharmacy_drugs')->onDelete('cascade');
            $table->string('location_type'); // CENTRAL_STORE, COMMAND_PHARMACY
            $table->foreignId('command_id')->nullable()->constrained('commands')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->timestamps();

            $table->index('pharmacy_drug_id');
            $table->index('location_type');
            $table->index('command_id');
            $table->index('expiry_date');
            $table->index(['pharmacy_drug_id', 'location_type', 'command_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_stocks');
    }
};
