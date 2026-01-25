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
        Schema::create('pharmacy_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('DRAFT'); // DRAFT, SUBMITTED, APPROVED, REJECTED, ISSUED, DISPENSED
            $table->string('reference_number')->nullable()->unique();
            $table->foreignId('command_id')->constrained('commands')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->unsignedInteger('current_step_order')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('command_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_requisitions');
    }
};
