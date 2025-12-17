<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emoluments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->foreignId('timeline_id')->constrained('emolument_timelines');
            $table->integer('year');
            $table->string('bank_name', 255);
            $table->string('bank_account_number', 50);
            $table->string('pfa_name', 255);
            $table->string('rsa_pin', 50);
            $table->enum('status', ['RAISED', 'ASSESSED', 'VALIDATED', 'PROCESSED', 'REJECTED'])->default('RAISED');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['officer_id', 'year']);
            $table->index('status');
            $table->index('timeline_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emoluments');
    }
};

