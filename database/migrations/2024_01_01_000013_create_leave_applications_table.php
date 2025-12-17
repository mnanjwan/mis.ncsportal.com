<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('number_of_days');
            $table->text('reason')->nullable();
            $table->date('expected_date_of_delivery')->nullable(); // EDD for maternity leave
            $table->enum('status', ['PENDING', 'MINUTED', 'APPROVED', 'REJECTED', 'CANCELLED', 'COMPLETED'])->default('PENDING');
            $table->string('medical_certificate_url', 500)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('minuted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('alert_sent_72h')->default(false);
            $table->timestamps();

            $table->index('officer_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};

