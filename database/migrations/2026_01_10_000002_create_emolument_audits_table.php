<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emolument_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emolument_id')->constrained('emoluments')->cascadeOnDelete();
            $table->foreignId('validation_id')->constrained('emolument_validations');
            $table->foreignId('auditor_id')->constrained('users');
            $table->enum('audit_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('comments')->nullable();
            $table->timestamp('audited_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emolument_audits');
    }
};

