<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = [
            [
                'name' => 'Controller Procurement',
                'code' => 'controller_procurement',
                'description' => 'Controller Procurement - Creates and submits procurement drafts for pharmacy supplies',
                'access_level' => 3,
            ],
            [
                'name' => 'OC Pharmacy',
                'code' => 'oc_pharmacy',
                'description' => 'OC Pharmacy - Approves procurements and requisitions, generates reports, oversees pharmacy operations',
                'access_level' => 4,
            ],
            [
                'name' => 'Central Medical Store',
                'code' => 'central_medical_store',
                'description' => 'Central Medical Store - Receives procurements, issues requisitions, manages central stock',
                'access_level' => 3,
            ],
            [
                'name' => 'Command Pharmacist',
                'code' => 'command_pharmacist',
                'description' => 'Command Pharmacist - Creates requisitions, manages command pharmacy stock, dispenses drugs',
                'access_level' => 2,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->whereIn('name', [
                'Controller Procurement',
                'OC Pharmacy',
                'Central Medical Store',
                'Command Pharmacist',
            ])
            ->delete();
    }
};
