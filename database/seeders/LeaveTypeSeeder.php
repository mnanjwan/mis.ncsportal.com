<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'ANNUAL_LEAVE',
                'max_duration_days' => 30,
                'max_occurrences_per_year' => 2,
                'requires_medical_certificate' => false,
                'description' => 'Can be applied in parts but maximum of 2 times in a year within the stipulated calendar days for officers. 28 Days for GL 07 and Below, 30 days for Level 08 and above.',
            ],
            [
                'name' => 'Leave on Permanent invalidation',
                'code' => 'PERMANENT_INVALIDATION',
                'max_duration_months' => 2,
                'requires_medical_certificate' => true,
                'description' => '2 months to be recommended by a Medical Officer',
            ],
            [
                'name' => 'Deferred Leave',
                'code' => 'DEFERRED_LEAVE',
                'description' => 'Deferred Leave',
            ],
            [
                'name' => 'Casual Leave',
                'code' => 'CASUAL_LEAVE',
                'max_duration_days' => 7,
                'description' => '7 working days and it must be when you\'ve exhausted your Annual leave',
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SICK_LEAVE',
                'requires_medical_certificate' => true,
                'description' => 'Must be recommended by a medical officer, no duration specified',
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'MATERNITY_LEAVE',
                'max_duration_days' => 112,
                'description' => '112 working days a year. Expected Date of Delivery (EDD) required.',
            ],
            [
                'name' => 'Maternity Leave on adoption of Child Under 4 months',
                'code' => 'MATERNITY_LEAVE_ADOPTION',
                'max_duration_days' => 84,
                'description' => '84 working days a year',
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PATERNITY_LEAVE',
                'max_duration_days' => 14,
                'description' => '14 working days a year',
            ],
            [
                'name' => 'Paternity Leave on adoption of Child Under 4 months',
                'code' => 'PATERNITY_LEAVE_ADOPTION',
                'max_duration_days' => 14,
                'description' => '14 working days a year',
            ],
            [
                'name' => 'Examination Leave',
                'code' => 'EXAMINATION_LEAVE',
                'description' => 'Examination Leave',
            ],
            [
                'name' => 'Leave for Compulsory Examination',
                'code' => 'COMPULSORY_EXAMINATION',
                'description' => 'Leave for Compulsory Examination',
            ],
            [
                'name' => 'Leave for non compulsory examination',
                'code' => 'NON_COMPULSORY_EXAMINATION',
                'description' => 'Leave for non compulsory examination',
            ],
            [
                'name' => 'Sabbatical Leave',
                'code' => 'SABBATICAL_LEAVE',
                'max_duration_months' => 12,
                'description' => '12 calendar months once every 5 years',
            ],
            [
                'name' => 'Study Leave without pay',
                'code' => 'STUDY_LEAVE_WITHOUT_PAY',
                'max_duration_months' => 48,
                'description' => '4 years in the first instance but you can apply for an extension',
            ],
            [
                'name' => 'Study Leave with pay',
                'code' => 'STUDY_LEAVE_WITH_PAY',
                'max_duration_months' => 36,
                'description' => '3 years with an extension of 1 year',
            ],
            [
                'name' => 'Leave on compassionate grounds',
                'code' => 'COMPASSIONATE_LEAVE',
                'description' => 'Leave on compassionate grounds',
            ],
            [
                'name' => 'Pre-retirement leave',
                'code' => 'PRE_RETIREMENT_LEAVE',
                'description' => 'Pre-retirement leave',
            ],
            [
                'name' => 'Leave of absence',
                'code' => 'LEAVE_OF_ABSENCE',
                'description' => 'Leave of absence',
            ],
            [
                'name' => 'Leave on grounds on urgent private affairs',
                'code' => 'URGENT_PRIVATE_AFFAIRS',
                'description' => 'Leave on grounds on urgent private affairs',
            ],
            [
                'name' => 'Leave for cultural and sporting activities',
                'code' => 'CULTURAL_SPORTING',
                'description' => 'Leave for cultural and sporting activities',
            ],
            [
                'name' => 'Leave to take part in trade Union activities',
                'code' => 'TRADE_UNION_ACTIVITIES',
                'description' => 'Leave to take part in trade Union activities',
            ],
            [
                'name' => 'Leave for in-service training',
                'code' => 'IN_SERVICE_TRAINING',
                'max_duration_months' => 48,
                'description' => '4 years maximum',
            ],
            [
                'name' => 'Leave of absence to Join Spouse on course of instruction abroad',
                'code' => 'JOIN_SPOUSE_ABROAD',
                'max_duration_months' => 9,
                'description' => '9 months',
            ],
            [
                'name' => 'Special leave to join spouse on ground of public policy',
                'code' => 'JOIN_SPOUSE_PUBLIC_POLICY',
                'description' => 'Special leave to join spouse on ground of public policy',
            ],
            [
                'name' => 'Leave of absence on grounds of public policy for technical aid program',
                'code' => 'TECHNICAL_AID_PROGRAM',
                'description' => 'Leave of absence on grounds of public policy for technical aid program',
            ],
            [
                'name' => 'Leave of absence for Special or Personal assistants on ground of public policy',
                'code' => 'SPECIAL_ASSISTANTS',
                'description' => 'Leave of absence for Special or Personal assistants on ground of public policy',
            ],
            [
                'name' => 'Leave of absence for Spouse of President, Vice President, Governor and Deputy Governor',
                'code' => 'SPOUSE_EXECUTIVE',
                'description' => 'Leave of absence for Spouse of President, Vice President, Governor and Deputy Governor',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
}

