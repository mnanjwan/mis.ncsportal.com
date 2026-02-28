<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores Expo push token (ExponentPushToken[...]) for mobile push notifications.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('push_token', 512)->nullable()->after('last_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('push_token');
        });
    }
};
