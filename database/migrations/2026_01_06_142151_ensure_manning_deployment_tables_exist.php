<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration ensures the manning_deployments tables exist,
     * even if the original migrations were marked as run but the tables don't exist.
     */
    public function up(): void
    {
        // Create manning_deployments table if it doesn't exist
        if (!Schema::hasTable('manning_deployments')) {
            Schema::create('manning_deployments', function (Blueprint $table) {
                $table->id();
                $table->string('deployment_number')->unique();
                $table->enum('status', ['DRAFT', 'PUBLISHED'])->default('DRAFT');
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('published_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('deployment_number');
            });
        }

        // Create manning_deployment_assignments table if it doesn't exist
        if (!Schema::hasTable('manning_deployment_assignments')) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop tables in down() to avoid data loss
        // If you need to rollback, do it manually
    }
};
