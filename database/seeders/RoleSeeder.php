<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'HRD',
                'code' => 'HRD',
                'description' => 'Human Resources Department',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Staff Officer',
                'code' => 'STAFF_OFFICER',
                'description' => 'Staff Officer - Administrative Manager',
                'access_level' => 'command_level',
            ],
            [
                'name' => 'Building Unit',
                'code' => 'BUILDING_UNIT',
                'description' => 'Building Unit - Accommodation Manager',
                'access_level' => 'command_level',
            ],
            [
                'name' => 'Establishment',
                'code' => 'ESTABLISHMENT',
                'description' => 'Establishment - Service Number Administrator',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Accounts',
                'code' => 'ACCOUNTS',
                'description' => 'Accounts - Financial Processor',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Board',
                'code' => 'BOARD',
                'description' => 'Board - Career Progression Manager',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Assessor',
                'code' => 'ASSESSOR',
                'description' => 'Assessor - Emolument Reviewer',
                'access_level' => 'command_level',
            ],
            [
                'name' => 'Validator',
                'code' => 'VALIDATOR',
                'description' => 'Validator - Final Emolument Approver',
                'access_level' => 'command_level',
            ],
            [
                'name' => 'Officer',
                'code' => 'OFFICER',
                'description' => 'Officer - End User',
                'access_level' => 'personal',
            ],
            [
                'name' => 'Area Controller',
                'code' => 'AREA_CONTROLLER',
                'description' => 'Area Controller - Senior Validator',
                'access_level' => 'command_level',
            ],
            [
                'name' => 'DC Admin',
                'code' => 'DC_ADMIN',
                'description' => 'DC Admin - Operational Approver',
                'access_level' => 'command_level',
            ],
            [
                'name' => 'Welfare',
                'code' => 'WELFARE',
                'description' => 'Welfare - Benefits Administrator',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Zone Coordinator',
                'code' => 'ZONE_COORDINATOR',
                'description' => 'Zone Coordinator - Zonal Posting Manager',
                'access_level' => 'zone_level',
            ],
            [
                'name' => 'TRADOC',
                'code' => 'TRADOC',
                'description' => 'TRADOC - Training Command',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'ICT',
                'code' => 'ICT',
                'description' => 'ICT - Information and Communication Technology',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Investigation Unit',
                'code' => 'INVESTIGATION_UNIT',
                'description' => 'Investigation Unit - Disciplinary Investigation Manager',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'CGC',
                'code' => 'CGC',
                'description' => 'Comptroller General of Customs - Preretirement Leave Authority',
                'access_level' => 'system_wide',
            ],
            [
                'name' => 'Admin',
                'code' => 'ADMIN',
                'description' => 'Admin - Command Role Assignment Manager',
                'access_level' => 'command_level',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}

