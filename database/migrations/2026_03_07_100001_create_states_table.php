<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geopolitical_zone_id')->constrained('geopolitical_zones')->cascadeOnDelete();
            $table->string('name', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['geopolitical_zone_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
