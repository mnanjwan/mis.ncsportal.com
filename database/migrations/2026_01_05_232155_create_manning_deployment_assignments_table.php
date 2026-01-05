<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manning_deployment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manning_deployment_id')->constrained('manning_deployments')->cascadeOnDelete();
            $table->foreignId('manning_request_id')->nullable()->constrained('manning_requests')->nullOnDelete();
            $table->foreignId('manning_request_item_id')->nullable()->constrained('manning_request_items')->nullOnDelete();
            $table->foreignId('officer_id')->constrained('officers');
            $table->foreignId('from_command_id')->nullable()->constrained('commands')->nullOnDelete();
            $table->foreignId('to_command_id')->constrained('commands');
            $table->string('rank')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('manning_deployment_id');
            $table->index('officer_id');
            $table->index('to_command_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manning_deployment_assignments');
    }
};
