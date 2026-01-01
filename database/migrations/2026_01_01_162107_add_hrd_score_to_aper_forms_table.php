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
        Schema::table('aper_forms', function (Blueprint $table) {
            $table->decimal('hrd_score', 8, 2)->nullable()->after('finalized_at');
            $table->text('hrd_score_notes')->nullable()->after('hrd_score');
            $table->timestamp('hrd_graded_at')->nullable()->after('hrd_score_notes');
            $table->foreignId('hrd_graded_by')->nullable()->constrained('users')->after('hrd_graded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aper_forms', function (Blueprint $table) {
            $table->dropForeign(['hrd_graded_by']);
            $table->dropColumn(['hrd_score', 'hrd_score_notes', 'hrd_graded_at', 'hrd_graded_by']);
        });
    }
};
