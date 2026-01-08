<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns exist before adding (safe for re-running migration)
        if (!Schema::hasColumn('officer_postings', 'release_letter_printed')) {
            Schema::table('officer_postings', function (Blueprint $table) {
                $table->boolean('release_letter_printed')->default(false)->after('released_at');
            });
        }
        
        if (!Schema::hasColumn('officer_postings', 'release_letter_printed_at')) {
            Schema::table('officer_postings', function (Blueprint $table) {
                $table->timestamp('release_letter_printed_at')->nullable()->after('release_letter_printed');
            });
        }
        
        if (!Schema::hasColumn('officer_postings', 'release_letter_printed_by')) {
            Schema::table('officer_postings', function (Blueprint $table) {
                $table->foreignId('release_letter_printed_by')->nullable()->after('release_letter_printed_at')->constrained('users')->nullOnDelete();
            });
        }
        
        if (!Schema::hasColumn('officer_postings', 'accepted_by_new_command')) {
            Schema::table('officer_postings', function (Blueprint $table) {
                $table->boolean('accepted_by_new_command')->default(false)->after('release_letter_printed_by');
            });
        }
        
        if (!Schema::hasColumn('officer_postings', 'accepted_at')) {
            Schema::table('officer_postings', function (Blueprint $table) {
                $table->timestamp('accepted_at')->nullable()->after('accepted_by_new_command');
            });
        }
        
        if (!Schema::hasColumn('officer_postings', 'accepted_by')) {
            Schema::table('officer_postings', function (Blueprint $table) {
                $table->foreignId('accepted_by')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
            });
        }
        
        // Add indexes (use raw SQL to check if they exist first to avoid errors)
        $connection = Schema::getConnection();
        $indexes = $connection->select("SHOW INDEXES FROM officer_postings WHERE Key_name IN ('idx_op_release_printed', 'idx_op_accepted', 'idx_op_release_accepted')");
        $existingIndexNames = collect($indexes)->pluck('Key_name')->toArray();
        
        Schema::table('officer_postings', function (Blueprint $table) use ($existingIndexNames) {
            if (!in_array('idx_op_release_printed', $existingIndexNames)) {
                $table->index('release_letter_printed', 'idx_op_release_printed');
            }
            if (!in_array('idx_op_accepted', $existingIndexNames)) {
                $table->index('accepted_by_new_command', 'idx_op_accepted');
            }
            if (!in_array('idx_op_release_accepted', $existingIndexNames)) {
                $table->index(['release_letter_printed', 'accepted_by_new_command'], 'idx_op_release_accepted');
            }
        });
    }

    public function down(): void
    {
        Schema::table('officer_postings', function (Blueprint $table) {
            $table->dropIndex('idx_op_release_accepted');
            $table->dropIndex('idx_op_accepted');
            $table->dropIndex('idx_op_release_printed');
            
            $table->dropConstrainedForeignId('accepted_by');
            $table->dropColumn('accepted_at');
            $table->dropColumn('accepted_by_new_command');
            
            $table->dropConstrainedForeignId('release_letter_printed_by');
            $table->dropColumn('release_letter_printed_at');
            $table->dropColumn('release_letter_printed');
        });
    }
};
