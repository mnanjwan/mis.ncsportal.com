<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 100)->unique();
            $table->integer('criteria_months_at_station')->nullable();
            $table->foreignId('manning_request_id')->nullable()->constrained('manning_requests')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['DRAFT', 'ACTIVE', 'COMPLETED'])->default('DRAFT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_orders');
    }
};

