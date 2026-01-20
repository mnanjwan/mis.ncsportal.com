<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicle_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $table->foreignId('command_id')->nullable()->constrained('commands')->nullOnDelete(); // where received/allocated to (area/unit)

            $table->date('date_of_allocation')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('received_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('command_id');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicle_receipts');
    }
};

