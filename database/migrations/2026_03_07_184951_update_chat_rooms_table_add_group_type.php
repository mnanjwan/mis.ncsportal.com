<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Make command_id nullable
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('command_id')->nullable()->change();
        });

        // Step 2: Expand enum to include 'group' via raw SQL (MySQL)
        DB::statement("ALTER TABLE chat_rooms MODIFY COLUMN room_type ENUM('COMMAND', 'MANAGEMENT', 'UNIT', 'group') DEFAULT 'COMMAND'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chat_rooms MODIFY COLUMN room_type ENUM('COMMAND', 'MANAGEMENT', 'UNIT') DEFAULT 'COMMAND'");

        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('command_id')->nullable(false)->change();
        });
    }
};
