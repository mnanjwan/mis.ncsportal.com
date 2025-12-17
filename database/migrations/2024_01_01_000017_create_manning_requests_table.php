<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manning_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('command_id')->constrained('commands');
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'FULFILLED'])->default('DRAFT');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('officers')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('forwarded_to_hrd_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('command_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manning_requests');
    }
};

