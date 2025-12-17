<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_application_id')->unique()->constrained('leave_applications')->cascadeOnDelete();
            $table->foreignId('staff_officer_id')->constrained('users');
            $table->foreignId('dc_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('area_controller_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->enum('approval_status', ['MINUTED', 'APPROVED', 'REJECTED'])->default('MINUTED');
            $table->timestamp('minuted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};

