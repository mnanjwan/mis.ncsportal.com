<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->foreignId('quarter_id')->constrained('quarters');
            $table->date('allocated_date');
            $table->date('deallocated_date')->nullable();
            $table->boolean('is_current')->default(true);
            $table->foreignId('allocated_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_quarters');
    }
};

