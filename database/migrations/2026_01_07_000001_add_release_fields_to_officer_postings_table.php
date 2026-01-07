<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officer_postings', function (Blueprint $table) {
            $table->timestamp('released_at')->nullable()->after('documented_at');
            $table->foreignId('released_by')->nullable()->after('released_at')->constrained('users')->nullOnDelete();

            $table->index('released_at');
            $table->index('released_by');
        });
    }

    public function down(): void
    {
        Schema::table('officer_postings', function (Blueprint $table) {
            $table->dropIndex(['released_at']);
            $table->dropIndex(['released_by']);
            $table->dropConstrainedForeignId('released_by');
            $table->dropColumn('released_at');
        });
    }
};


