<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emolument_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emolument_id')->constrained('emoluments')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('emolument_assessments');
            $table->foreignId('validator_id')->constrained('users');
            $table->enum('validation_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('comments')->nullable();
            $table->timestamp('validated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emolument_validations');
    }
};

