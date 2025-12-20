<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('next_of_kin', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('next_of_kin', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
