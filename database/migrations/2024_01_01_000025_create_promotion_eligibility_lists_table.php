<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_eligibility_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->timestamp('generated_at')->useCurrent();
            $table->foreignId('generated_by')->constrained('users');
            $table->enum('status', ['DRAFT', 'FINALIZED', 'SUBMITTED_TO_BOARD'])->default('DRAFT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_eligibility_lists');
    }
};

