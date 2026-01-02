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
        Schema::create('officer_deletion_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->nullable();
            $table->string('officer_name');
            $table->string('service_number')->nullable();
            $table->string('rank')->nullable();
            $table->string('command')->nullable();
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deleted_by_name');
            $table->string('deleted_by_role'); // HRD or Establishment
            $table->text('reason')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();

            $table->index('appointment_number');
            $table->index('deleted_by_user_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officer_deletion_audit_logs');
    }
};
