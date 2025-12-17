<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->foreignId('command_id')->constrained('commands');
            $table->foreignId('staff_order_id')->nullable()->constrained('staff_orders')->nullOnDelete();
            $table->foreignId('movement_order_id')->nullable()->constrained('movement_orders')->nullOnDelete();
            $table->date('posting_date');
            $table->boolean('is_current')->default(true);
            $table->timestamp('documented_at')->nullable();
            $table->foreignId('documented_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('officer_id');
            $table->index('command_id');
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_postings');
    }
};

