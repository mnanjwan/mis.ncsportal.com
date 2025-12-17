<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers');
            $table->foreignId('command_id')->constrained('commands');
            $table->string('letter_number', 100)->unique();
            $table->date('release_date');
            $table->text('reason')->nullable();
            $table->foreignId('prepared_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_letters');
    }
};

