<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_staff_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('command_id')->constrained('commands');
            $table->string('order_number', 100)->unique();
            $table->date('order_date');
            $table->foreignId('prepared_by')->constrained('users');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_staff_orders');
    }
};

