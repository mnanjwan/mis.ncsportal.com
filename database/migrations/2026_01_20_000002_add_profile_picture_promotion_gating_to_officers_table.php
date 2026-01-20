<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            // When the officer last updated their profile picture (onboarding upload counts)
            $table->timestamp('profile_picture_updated_at')->nullable();

            // When the officer is required to update their profile picture due to promotion approval
            $table->timestamp('profile_picture_required_after_promotion_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn([
                'profile_picture_updated_at',
                'profile_picture_required_after_promotion_at',
            ]);
        });
    }
};

