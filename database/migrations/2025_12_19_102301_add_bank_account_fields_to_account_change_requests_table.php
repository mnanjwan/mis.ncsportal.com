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
        Schema::table('account_change_requests', function (Blueprint $table) {
            $table->string('new_bank_name', 255)->nullable()->after('new_rsa_pin');
            $table->string('new_sort_code', 50)->nullable()->after('new_bank_name');
            $table->string('new_pfa_name', 255)->nullable()->after('new_sort_code');
            $table->string('current_bank_name', 255)->nullable()->after('current_rsa_pin');
            $table->string('current_sort_code', 50)->nullable()->after('current_bank_name');
            $table->string('current_pfa_name', 255)->nullable()->after('current_sort_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_change_requests', function (Blueprint $table) {
            $table->dropColumn([
                'new_bank_name',
                'new_sort_code',
                'new_pfa_name',
                'current_bank_name',
                'current_sort_code',
                'current_pfa_name',
            ]);
        });
    }
};
