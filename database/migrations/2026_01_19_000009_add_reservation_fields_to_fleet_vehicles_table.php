<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_vehicles', function (Blueprint $table) {
            $table->foreignId('reserved_fleet_request_id')
                ->nullable()
                ->after('current_officer_id')
                ->constrained('fleet_requests')
                ->nullOnDelete();

            $table->foreignId('reserved_by_user_id')
                ->nullable()
                ->after('reserved_fleet_request_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('reserved_at')->nullable()->after('reserved_by_user_id');

            $table->index('reserved_fleet_request_id');
            $table->index('reserved_at');
        });
    }

    public function down(): void
    {
        Schema::table('fleet_vehicles', function (Blueprint $table) {
            $table->dropIndex(['reserved_fleet_request_id']);
            $table->dropIndex(['reserved_at']);

            $table->dropConstrainedForeignId('reserved_by_user_id');
            $table->dropConstrainedForeignId('reserved_fleet_request_id');

            $table->dropColumn('reserved_at');
        });
    }
};

