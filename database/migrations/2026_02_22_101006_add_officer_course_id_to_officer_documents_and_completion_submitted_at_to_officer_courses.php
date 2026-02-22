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
        Schema::table('officer_documents', function (Blueprint $table) {
            $table->foreignId('officer_course_id')->nullable()->after('officer_id')->constrained('officer_courses')->nullOnDelete();
        });

        Schema::table('officer_courses', function (Blueprint $table) {
            $table->timestamp('completion_submitted_at')->nullable()->after('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officer_documents', function (Blueprint $table) {
            $table->dropForeign(['officer_course_id']);
        });

        Schema::table('officer_courses', function (Blueprint $table) {
            $table->dropColumn('completion_submitted_at');
        });
    }
};
