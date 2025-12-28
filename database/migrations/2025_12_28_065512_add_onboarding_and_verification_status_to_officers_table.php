<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->enum('onboarding_status', ['pending', 'link_sent', 'in_progress', 'completed', 'verified'])->default('pending')->after('is_active');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')->after('onboarding_status');
            $table->timestamp('onboarding_link_sent_at')->nullable()->after('verification_status');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_link_sent_at');
            $table->timestamp('verified_at')->nullable()->after('onboarding_completed_at');
            $table->text('verification_notes')->nullable()->after('verified_at');
            $table->string('onboarding_token', 100)->nullable()->unique()->after('verification_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_status',
                'verification_status',
                'onboarding_link_sent_at',
                'onboarding_completed_at',
                'verified_at',
                'verification_notes',
                'onboarding_token'
            ]);
        });
    }
};
