<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_type', 50); // COMMAND_REQUISITION, NEW_VEHICLES_DELIVERY_PROPOSAL
            $table->string('status', 50)->default('DRAFT');

            $table->foreignId('origin_command_id')->nullable()->constrained('commands')->nullOnDelete();
            $table->foreignId('target_command_id')->nullable()->constrained('commands')->nullOnDelete();

            $table->string('requested_vehicle_type', 20)->nullable(); // SALOON, SUV, BUS
            $table->string('requested_make', 100)->nullable();
            $table->string('requested_model', 100)->nullable();
            $table->unsignedSmallInteger('requested_year')->nullable();
            $table->unsignedInteger('requested_quantity')->default(1);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('submitted_at')->nullable();

            $table->unsignedSmallInteger('current_step_order')->nullable();

            $table->timestamps();

            $table->index('request_type');
            $table->index('status');
            $table->index('origin_command_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_requests');
    }
};

