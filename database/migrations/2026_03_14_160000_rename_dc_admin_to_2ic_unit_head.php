<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename role "DC Admin" to "2iC Unit Head".
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'DC Admin')
            ->update([
                'name' => '2iC Unit Head',
                'code' => '2IC_UNIT_HEAD',
                'description' => '2iC Unit Head - Operational Approver',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('name', '2iC Unit Head')
            ->update([
                'name' => 'DC Admin',
                'code' => 'DC_ADMIN',
                'description' => 'DC Admin - Operational Approver',
                'updated_at' => now(),
            ]);
    }
};
