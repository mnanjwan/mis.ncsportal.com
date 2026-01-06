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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manning_deployments');
    }
};
