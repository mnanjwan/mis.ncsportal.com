<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicle_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fleet_vehicle_assignment_id')
                ->constrained('fleet_vehicle_assignments')
                ->cascadeOnDelete();

            $table->foreignId('returned_by_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('returned_at')->nullable();
            $table->text('condition_notes')->nullable();

            $table->timestamps();

            $table->index('returned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicle_returns');
    }
};

