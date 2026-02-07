<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_vehicles', function (Blueprint $table) {
            $table->foreignId('vehicle_model_id')->nullable()->after('id')->constrained('fleet_vehicle_models')->nullOnDelete();
            $table->index('vehicle_model_id');
        });
    }

    public function down(): void
    {
        Schema::table('fleet_vehicles', function (Blueprint $table) {
            $table->dropForeign(['vehicle_model_id']);
            $table->dropIndex(['vehicle_model_id']);
            $table->dropColumn('vehicle_model_id');
        });
    }
};
