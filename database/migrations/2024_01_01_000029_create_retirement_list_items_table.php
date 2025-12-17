<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retirement_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retirement_list_id')->constrained('retirement_list')->cascadeOnDelete();
            $table->foreignId('officer_id')->constrained('officers');
            $table->integer('serial_number');
            $table->string('rank', 100);
            $table->string('initials', 50);
            $table->string('name', 255);
            $table->enum('retirement_condition', ['AGE', 'SVC']);
            $table->date('date_of_birth');
            $table->date('date_of_first_appointment');
            $table->date('date_of_pre_retirement_leave');
            $table->date('retirement_date');
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retirement_list_items');
    }
};

