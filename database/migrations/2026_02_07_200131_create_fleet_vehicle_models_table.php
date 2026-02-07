<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->string('make', 100);
            $table->string('vehicle_type', 20); // SALOON, SUV, BUS, PICKUP
            $table->unsignedSmallInteger('year_of_manufacture');
            $table->timestamps();

            // Unique constraint: Make + Vehicle Type + Year combination must be unique
            $table->unique(['make', 'vehicle_type', 'year_of_manufacture'], 'unique_vehicle_model');
            
            $table->index('make');
            $table->index('vehicle_type');
            $table->index('year_of_manufacture');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicle_models');
    }
};
