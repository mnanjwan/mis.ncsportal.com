<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deceased_officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->unique()->constrained('officers')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamp('reported_at')->useCurrent();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->string('death_certificate_url', 500)->nullable();
            $table->date('date_of_death');
            $table->json('next_of_kin_data')->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('rsa_administrator', 255)->nullable();
            $table->boolean('benefits_processed')->default(false);
            $table->timestamp('benefits_processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deceased_officers');
    }
};

