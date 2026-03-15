<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('onboarding_document_categories')) {
            return;
        }

        Schema::table('onboarding_document_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('onboarding_document_categories', 'applies_to')) {
                $table->string('applies_to')->default('both')->after('name');
            }
        });

        DB::table('onboarding_document_categories')
            ->whereNull('applies_to')
            ->update(['applies_to' => 'both']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('onboarding_document_categories')) {
            return;
        }

        if (!Schema::hasColumn('onboarding_document_categories', 'applies_to')) {
            return;
        }

        Schema::table('onboarding_document_categories', function (Blueprint $table) {
            $table->dropColumn('applies_to');
        });
    }
};
