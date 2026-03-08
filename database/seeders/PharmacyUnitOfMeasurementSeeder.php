<?php

namespace Database\Seeders;

use App\Models\PharmacyUnitOfMeasurement;
use Illuminate\Database\Seeder;

class PharmacyUnitOfMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            'Tablet (Tab)',
            'Capsule (Cap)',
            'Sachet',
            'Pack',
            'Strip',
            'Bottle',
            'Tin',
            'Jar',
            'Blister Pack',
            'Box',
            'Dose',
            'Milliliter (mL)',
            'Liter (L)',
            'Vial',
            'Ampoule (Amp)',
            'Infusion Bag',
            'Drop (gtt)',
            'Tube',
            'Pot',
            'Pump Bottle',
            'Applicator',
            'Prefilled Syringe',
            'Cartridge',
            'Injection Pen',
            'Roll',
            'Piece (Pc)',
            'Pair',
            'Carton',
            'Set',
            'Pad',
            'Milligram (mg)',
            'Gram (g)',
            'Kilogram (kg)',
            'Microgram (mcg)',
        ];

        foreach ($units as $name) {
            PharmacyUnitOfMeasurement::firstOrCreate(['name' => $name]);
        }
    }
}
