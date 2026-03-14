<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename "Controller Pharmacy" to "Comptroller Pharmacy" and
     * "Controller Procurement" to "Comptroller Procurement".
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'Controller Pharmacy')
            ->update([
                'name' => 'Comptroller Pharmacy',
                'code' => 'comptroller_pharmacy',
                'description' => 'Comptroller Pharmacy - Approves procurements and requisitions, generates reports, oversees pharmacy operations',
                'updated_at' => now(),
            ]);

        DB::table('roles')
            ->where('name', 'Controller Procurement')
            ->update([
                'name' => 'Comptroller Procurement',
                'code' => 'comptroller_procurement',
                'description' => 'Comptroller Procurement - Creates and submits procurement drafts for pharmacy supplies',
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('pharmacy_workflow_steps')) {
            DB::table('pharmacy_workflow_steps')
                ->where('role_name', 'Controller Pharmacy')
                ->update(['role_name' => 'Comptroller Pharmacy']);
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'Comptroller Pharmacy')
            ->update([
                'name' => 'Controller Pharmacy',
                'code' => 'controller_pharmacy',
                'description' => 'Controller Pharmacy - Approves procurements and requisitions, generates reports, oversees pharmacy operations',
                'updated_at' => now(),
            ]);

        DB::table('roles')
            ->where('name', 'Comptroller Procurement')
            ->update([
                'name' => 'Controller Procurement',
                'code' => 'controller_procurement',
                'description' => 'Controller Procurement - Creates and submits procurement drafts for pharmacy supplies',
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('pharmacy_workflow_steps')) {
            DB::table('pharmacy_workflow_steps')
                ->where('role_name', 'Comptroller Pharmacy')
                ->update(['role_name' => 'Controller Pharmacy']);
        }
    }
};
