<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_request_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fleet_request_id')->constrained('fleet_requests')->cascadeOnDelete();

            $table->unsignedSmallInteger('step_order');
            $table->string('role_name', 100);
            $table->string('action', 20); // FORWARD, REVIEW, APPROVE

            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('acted_at')->nullable();
            $table->string('decision', 20)->nullable(); // FORWARDED, APPROVED, REJECTED, RETURNED
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(['fleet_request_id', 'step_order']);
            $table->index('role_name');
            $table->index('acted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_request_steps');
    }
};

