<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('officer_quarters', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('ACCEPTED')->after('is_current');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('accepted_at')->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('accepted_at');
        });

        // Update existing records: if is_current is true, set status to ACCEPTED and accepted_at to allocated_date
        DB::table('officer_quarters')
            ->where('is_current', true)
            ->update([
                'status' => 'ACCEPTED',
                'accepted_at' => DB::raw('allocated_date')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officer_quarters', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'accepted_at', 'rejected_at']);
        });
    }
};
