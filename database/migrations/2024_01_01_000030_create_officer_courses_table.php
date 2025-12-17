<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->string('course_name', 255);
            $table->string('course_type', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->date('completion_date')->nullable();
            $table->string('certificate_url', 500)->nullable();
            $table->foreignId('nominated_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_courses');
    }
};

