<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicles', function (Blueprint $table) {
            $table->id();

            $table->string('make', 100);
            $table->string('model', 100)->nullable();
            $table->unsignedSmallInteger('year_of_manufacture')->nullable();
            $table->string('vehicle_type', 20); // SALOON, SUV, BUS

            $table->string('reg_no', 50)->nullable()->unique();
            $table->string('chassis_number', 100)->unique();
            $table->string('engine_number', 100)->nullable()->unique();

            $table->string('service_status', 20)->default('SERVICEABLE'); // SERVICEABLE, UNSERVICEABLE
            $table->string('lifecycle_status', 30)->default('IN_STOCK'); // IN_STOCK, AT_COMMAND_POOL, IN_OFFICER_CUSTODY, RETIRED

            $table->foreignId('current_command_id')->nullable()->constrained('commands')->nullOnDelete();
            $table->foreignId('current_officer_id')->nullable()->constrained('officers')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('vehicle_type');
            $table->index('service_status');
            $table->index('lifecycle_status');
            $table->index('current_command_id');
            $table->index('current_officer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicles');
    }
};

