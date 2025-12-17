<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('code', 50)->unique();
            $table->integer('max_duration_days')->nullable();
            $table->integer('max_duration_months')->nullable();
            $table->integer('max_occurrences_per_year')->nullable();
            $table->boolean('requires_medical_certificate')->default(false);
            $table->string('requires_approval_level', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};

