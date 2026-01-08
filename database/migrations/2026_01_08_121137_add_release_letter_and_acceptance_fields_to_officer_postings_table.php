<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officer_postings', function (Blueprint $table) {
            // Check if columns exist before adding (safe for re-running migration)
            if (!Schema::hasColumn('officer_postings', 'release_letter_printed')) {
                $table->boolean('release_letter_printed')->default(false)->after('released_at');
            }
            if (!Schema::hasColumn('officer_postings', 'release_letter_printed_at')) {
                $table->timestamp('release_letter_printed_at')->nullable()->after('release_letter_printed');
            }
            if (!Schema::hasColumn('officer_postings', 'release_letter_printed_by')) {
                $table->foreignId('release_letter_printed_by')->nullable()->after('release_letter_printed_at')->constrained('users')->nullOnDelete();
            }
            
            // Acceptance tracking
            if (!Schema::hasColumn('officer_postings', 'accepted_by_new_command')) {
                $table->boolean('accepted_by_new_command')->default(false)->after('release_letter_printed_by');
            }
            if (!Schema::hasColumn('officer_postings', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('accepted_by_new_command');
            }
            if (!Schema::hasColumn('officer_postings', 'accepted_by')) {
                $table->foreignId('accepted_by')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
            }
        });
        
        // Add indexes separately (check if they exist first)
        Schema::table('officer_postings', function (Blueprint $table) {
            // Check if indexes exist before adding
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('officer_postings');
            
            if (!isset($indexesFound['idx_op_release_printed'])) {
                $table->index('release_letter_printed', 'idx_op_release_printed');
            }
            if (!isset($indexesFound['idx_op_accepted'])) {
                $table->index('accepted_by_new_command', 'idx_op_accepted');
            }
            if (!isset($indexesFound['idx_op_release_accepted'])) {
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
