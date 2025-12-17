<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('officers')->cascadeOnDelete();
            $table->text('message_text');
            $table->string('attachment_url', 500)->nullable();
            $table->boolean('is_broadcast')->default(false);
            $table->timestamps();

            $table->index('chat_room_id');
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};

