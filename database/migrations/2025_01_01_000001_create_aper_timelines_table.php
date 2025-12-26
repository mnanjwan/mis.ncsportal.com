<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aper_timelines', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_extended')->default(false);
            $table->date('extension_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aper_timelines');
    }
};

