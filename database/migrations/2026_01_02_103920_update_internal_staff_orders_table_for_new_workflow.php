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
        Schema::table('internal_staff_orders', function (Blueprint $table) {
            // Add new fields for officer assignment workflow
            $table->foreignId('officer_id')->nullable()->after('command_id')->constrained('officers')->onDelete('cascade');
            $table->string('current_unit')->nullable()->after('officer_id');
            $table->enum('current_role', ['OIC', '2IC', 'Member'])->nullable()->after('current_unit');
            $table->string('target_unit')->nullable()->after('current_role');
            $table->enum('target_role', ['OIC', '2IC', 'Member'])->nullable()->after('target_unit');
            
            // Add approval workflow fields
            $table->enum('status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED'])->default('DRAFT')->after('target_role');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_staff_orders', function (Blueprint $table) {
            // Check if columns exist before dropping
            if (Schema::hasColumn('internal_staff_orders', 'officer_id')) {
                $table->dropForeign(['officer_id']);
            }
            if (Schema::hasColumn('internal_staff_orders', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
            
            $columnsToDrop = [
                'officer_id',
                'current_unit',
                'current_role',
                'target_unit',
                'target_role',
                'status',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('internal_staff_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
