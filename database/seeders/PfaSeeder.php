<?php

namespace Database\Seeders;

use App\Models\Pfa;
use Illuminate\Database\Seeder;

class PfaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pfas = [
            'Access Pensions',
            'ARM Pension',
            'AXA Mansard Pensions',
            'Crusader Pensions',
            'Fidelity Pensions',
            'First Guarantee Pensions',
            'Future Unity Glanvills Pensions',
            'IEI-Anchor Pensions',
            'Leadway Pensure PFA',
            'Nigerian University Pension',
            'NLPC Pension Fund Administrators',
            'Oak Pensions',
            'PAL Pensions',
            'Pension Alliance Limited',
            'Premium Pensions',
            'Radix Pension Managers',
            'Sigma Pensions',
            'Stanbic IBTC Pension Managers',
            'Tangerine Pensions',
            'Trustfund Pensions',
            'Veritas Glanvills Pensions',
        ];

        foreach ($pfas as $name) {
            Pfa::updateOrCreate(
                ['name' => $name],
                ['rsa_prefix' => 'PEN', 'rsa_digits' => 12, 'is_active' => true]
            );
        }
    }
}
