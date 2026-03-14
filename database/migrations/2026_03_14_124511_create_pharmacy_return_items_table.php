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
        Schema::create('pharmacy_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_return_id')->constrained('pharmacy_returns')->onDelete('cascade');
            $table->foreignId('pharmacy_drug_id')->constrained();
            $table->integer('quantity');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_return_items');
    }
};
