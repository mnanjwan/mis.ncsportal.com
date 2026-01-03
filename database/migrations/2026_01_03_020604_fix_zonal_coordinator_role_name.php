<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update role name from "Zonal Coordinator" to "Zone Coordinator"
        DB::table('roles')
            ->where('name', 'Zonal Coordinator')
            ->where('code', 'ZONE_COORDINATOR')
            ->update(['name' => 'Zone Coordinator']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert role name back to "Zonal Coordinator"
        DB::table('roles')
            ->where('name', 'Zone Coordinator')
            ->where('code', 'ZONE_COORDINATOR')
            ->update(['name' => 'Zonal Coordinator']);
    }
};
