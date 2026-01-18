<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            'Access Bank Limited',
            'Citibank Nigeria Limited',
            'Ecobank Nigeria Limited',
            'Fidelity Bank Plc',
            'First Bank of Nigeria Limited',
            'First City Monument Bank Limited',
            'Globus Bank Limited',
            'Guaranty Trust Bank Limited',
            'Heritage Bank Plc',
            'Keystone Bank Limited',
            'Optimus Bank Limited',
            'Parallex Bank Limited',
            'Polaris Bank Limited',
            'Premium Trust Bank Limited',
            'Providus Bank Limited',
            'Stanbic IBTC Bank Limited',
            'Standard Chartered Bank Nigeria Limited',
            'Sterling Bank Limited',
            'SunTrust Bank Nigeria Limited',
            'Titan Trust Bank Limited',
            'Union Bank of Nigeria Plc',
            'United Bank for Africa Plc',
            'Unity Bank Plc',
            'Wema Bank Plc',
            'Zenith Bank Plc',
        ];

        foreach ($banks as $name) {
            Bank::updateOrCreate(
                ['name' => $name],
                ['account_number_digits' => 10, 'is_active' => true]
            );
        }
    }
}
