<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename role "OC Pharmacy" to "Controller Pharmacy" and update all references.
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'OC Pharmacy')
            ->update([
                'name' => 'Controller Pharmacy',
                'code' => 'controller_pharmacy',
                'description' => 'Controller Pharmacy - Approves procurements and requisitions, generates reports, oversees pharmacy operations',
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('pharmacy_workflow_steps')) {
            DB::table('pharmacy_workflow_steps')
                ->where('role_name', 'OC Pharmacy')
                ->update(['role_name' => 'Controller Pharmacy']);
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'Controller Pharmacy')
            ->update([
                'name' => 'OC Pharmacy',
                'code' => 'oc_pharmacy',
                'description' => 'OC Pharmacy - Approves procurements and requisitions, generates reports, oversees pharmacy operations',
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('pharmacy_workflow_steps')) {
            DB::table('pharmacy_workflow_steps')
                ->where('role_name', 'Controller Pharmacy')
                ->update(['role_name' => 'OC Pharmacy']);
        }
    }
};
