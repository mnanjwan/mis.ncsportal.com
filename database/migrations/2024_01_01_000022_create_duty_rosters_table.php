<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('command_id')->constrained('commands');
            $table->date('roster_period_start');
            $table->date('roster_period_end');
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('officers')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'ACTIVE'])->default('DRAFT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_rosters');
    }
};

