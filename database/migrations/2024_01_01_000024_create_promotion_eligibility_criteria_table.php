<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_eligibility_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('rank', 100);
            $table->decimal('years_in_rank_required', 4, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_eligibility_criteria');
    }
};

