<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pass_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('number_of_days'); // Max 5 days
            $table->text('reason')->nullable();
            $table->enum('status', ['PENDING', 'MINUTED', 'APPROVED', 'REJECTED', 'CANCELLED', 'COMPLETED'])->default('PENDING');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('minuted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->boolean('expiry_alert_sent')->default(false);
            $table->timestamps();

            $table->index('officer_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pass_applications');
    }
};

