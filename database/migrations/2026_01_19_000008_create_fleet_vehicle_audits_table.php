<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicle_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();

            $table->string('field_name', 30); // REG_NO, ENGINE_NO
            $table->string('old_value', 191)->nullable();
            $table->string('new_value', 191)->nullable();

            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('changed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['fleet_vehicle_id', 'field_name']);
            $table->index('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicle_audits');
    }
};

