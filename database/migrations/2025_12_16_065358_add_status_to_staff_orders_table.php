<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_orders', function (Blueprint $table) {
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'CANCELLED'])->default('DRAFT')->after('order_type');
            $table->text('description')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('staff_orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'description']);
        });
    }
};
