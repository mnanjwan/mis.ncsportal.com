<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fleet_requests', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->nullable();
            $table->foreignId('fleet_vehicle_id')->nullable()->constrained('fleet_vehicles')->nullOnDelete();
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('assigned_to_workshop_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_requests', function (Blueprint $table) {
            //
        });
    }
};
