<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_request_fulfillments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fleet_request_id')->constrained('fleet_requests')->cascadeOnDelete();

            $table->unsignedInteger('fulfilled_quantity')->default(0);
            $table->unsignedInteger('kiv_quantity')->default(0);

            $table->foreignId('fulfilled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fulfilled_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['fleet_request_id', 'fulfilled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_request_fulfillments');
    }
};

