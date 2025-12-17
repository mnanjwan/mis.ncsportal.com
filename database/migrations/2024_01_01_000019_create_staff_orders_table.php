<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 100)->unique();
            $table->foreignId('officer_id')->constrained('officers');
            $table->foreignId('from_command_id')->nullable()->constrained('commands')->nullOnDelete();
            $table->foreignId('to_command_id')->constrained('commands');
            $table->date('effective_date');
            $table->string('order_type')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_altered')->default(false);
            $table->timestamp('altered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_orders');
    }
};

