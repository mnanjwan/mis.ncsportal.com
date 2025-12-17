<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers');
            $table->foreignId('eligibility_list_item_id')->nullable()->constrained('promotion_eligibility_list_items')->nullOnDelete();
            $table->string('from_rank', 100);
            $table->string('to_rank', 100);
            $table->date('promotion_date');
            $table->boolean('approved_by_board')->default(false);
            $table->date('board_meeting_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};

