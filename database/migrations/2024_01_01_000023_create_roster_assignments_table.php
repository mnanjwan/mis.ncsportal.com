<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained('duty_rosters')->cascadeOnDelete();
            $table->foreignId('officer_id')->constrained('officers');
            $table->date('duty_date');
            $table->string('shift', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_assignments');
    }
};

