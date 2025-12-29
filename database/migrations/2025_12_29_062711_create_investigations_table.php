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
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->onDelete('cascade');
            $table->foreignId('investigation_officer_id')->constrained('users')->onDelete('cascade');
            $table->text('invitation_message')->nullable();
            $table->enum('status', ['INVITED', 'ONGOING_INVESTIGATION', 'INTERDICTED', 'SUSPENDED', 'RESOLVED'])->default('INVITED');
            $table->text('notes')->nullable();
            $table->dateTime('invited_at')->nullable();
            $table->dateTime('status_changed_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index('officer_id');
            $table->index('status');
            $table->index('investigation_officer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigations');
    }
};
