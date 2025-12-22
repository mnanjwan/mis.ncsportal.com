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
        // Add preretirement leave fields to retirement_list_items table
        Schema::table('retirement_list_items', function (Blueprint $table) {
            $table->enum('preretirement_leave_status', ['AUTO_PLACED', 'CGC_APPROVED_IN_OFFICE', 'CANCELLED'])->nullable()->after('notified_at');
            $table->timestamp('auto_placed_at')->nullable()->after('preretirement_leave_status');
            $table->foreignId('cgc_approved_by')->nullable()->after('auto_placed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('cgc_approved_at')->nullable()->after('cgc_approved_by');
            $table->text('cgc_approval_reason')->nullable()->after('cgc_approved_at');
        });

        // Add preretirement leave status to officers table
        Schema::table('officers', function (Blueprint $table) {
            $table->enum('preretirement_leave_status', ['NONE', 'ON_PRERETIREMENT_LEAVE', 'PRERETIREMENT_LEAVE_IN_OFFICE'])->default('NONE')->after('is_active');
            $table->timestamp('preretirement_leave_started_at')->nullable()->after('preretirement_leave_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from retirement_list_items table
        Schema::table('retirement_list_items', function (Blueprint $table) {
            $table->dropForeign(['cgc_approved_by']);
            $table->dropColumn([
                'preretirement_leave_status',
                'auto_placed_at',
                'cgc_approved_by',
                'cgc_approved_at',
                'cgc_approval_reason',
            ]);
        });

        // Remove fields from officers table
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn([
                'preretirement_leave_status',
                'preretirement_leave_started_at',
            ]);
        });
    }
};
