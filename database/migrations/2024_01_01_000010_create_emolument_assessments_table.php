<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emolument_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emolument_id')->constrained('emoluments')->cascadeOnDelete();
            $table->foreignId('assessor_id')->constrained('users');
            $table->enum('assessment_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('comments')->nullable();
            $table->timestamp('assessed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emolument_assessments');
    }
};

