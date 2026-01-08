<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officer_postings', function (Blueprint $table) {
            // Release letter tracking
            $table->boolean('release_letter_printed')->default(false)->after('released_at');
            $table->timestamp('release_letter_printed_at')->nullable()->after('release_letter_printed');
            $table->foreignId('release_letter_printed_by')->nullable()->after('release_letter_printed_at')->constrained('users')->nullOnDelete();
            
            // Acceptance tracking
            $table->boolean('accepted_by_new_command')->default(false)->after('release_letter_printed_by');
            $table->timestamp('accepted_at')->nullable()->after('accepted_by_new_command');
            $table->foreignId('accepted_by')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
            
            // Indexes for performance
            $table->index('release_letter_printed');
            $table->index('accepted_by_new_command');
            $table->index(['release_letter_printed', 'accepted_by_new_command']);
        });
    }

    public function down(): void
    {
        Schema::table('officer_postings', function (Blueprint $table) {
            $table->dropIndex(['release_letter_printed', 'accepted_by_new_command']);
            $table->dropIndex(['accepted_by_new_command']);
            $table->dropIndex(['release_letter_printed']);
            
            $table->dropConstrainedForeignId('accepted_by');
            $table->dropColumn('accepted_at');
            $table->dropColumn('accepted_by_new_command');
            
            $table->dropConstrainedForeignId('release_letter_printed_by');
            $table->dropColumn('release_letter_printed_at');
            $table->dropColumn('release_letter_printed');
        });
    }
};
