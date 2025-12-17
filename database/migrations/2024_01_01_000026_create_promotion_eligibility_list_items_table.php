<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_eligibility_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eligibility_list_id')->constrained('promotion_eligibility_lists')->cascadeOnDelete();
            $table->foreignId('officer_id')->constrained('officers');
            $table->integer('serial_number');
            $table->string('current_rank', 100);
            $table->decimal('years_in_rank', 4, 2);
            $table->date('date_of_first_appointment');
            $table->date('date_of_present_appointment');
            $table->string('state', 100);
            $table->date('date_of_birth');
            $table->string('excluded_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_eligibility_list_items');
    }
};

