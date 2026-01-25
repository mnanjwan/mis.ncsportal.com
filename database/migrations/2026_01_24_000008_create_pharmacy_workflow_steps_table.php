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
        Schema::create('pharmacy_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_procurement_id')->nullable()->constrained('pharmacy_procurements')->onDelete('cascade');
            $table->foreignId('pharmacy_requisition_id')->nullable()->constrained('pharmacy_requisitions')->onDelete('cascade');
            $table->unsignedInteger('step_order');
            $table->string('role_name');
            $table->string('action'); // FORWARD, APPROVE, REVIEW
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acted_at')->nullable();
            $table->string('decision')->nullable(); // FORWARDED, APPROVED, REJECTED, REVIEWED
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('pharmacy_procurement_id');
            $table->index('pharmacy_requisition_id');
            $table->index('step_order');
            $table->index('role_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_workflow_steps');
    }
};
