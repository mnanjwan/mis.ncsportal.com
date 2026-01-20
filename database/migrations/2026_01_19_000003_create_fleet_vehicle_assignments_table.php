<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicle_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();

            // Exactly one of these should be populated by the application logic
            $table->foreignId('assigned_to_command_id')->nullable()->constrained('commands')->nullOnDelete();
            $table->foreignId('assigned_to_officer_id')->nullable()->constrained('officers')->nullOnDelete();

            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();

            // Release to command: CC T&L releases; Area Controller receives (acknowledges)
            $table->foreignId('released_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('released_at')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('received_at')->nullable();

            $table->dateTime('ended_at')->nullable();
            $table->string('end_reason', 30)->nullable(); // RETURNED, REASSIGNED, RETIRED
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('fleet_vehicle_id');
            $table->index('assigned_to_command_id');
            $table->index('assigned_to_officer_id');
            $table->index('assigned_at');
            $table->index('ended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicle_assignments');
    }
};

