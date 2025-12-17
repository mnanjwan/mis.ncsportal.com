<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manning_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manning_request_id')->constrained('manning_requests')->cascadeOnDelete();
            $table->string('rank', 100);
            $table->integer('quantity_needed');
            $table->enum('sex_requirement', ['M', 'F', 'ANY'])->default('ANY');
            $table->string('qualification_requirement', 255)->nullable();
            $table->foreignId('matched_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manning_request_items');
    }
};

