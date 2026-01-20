<?php

namespace Database\Seeders;

use App\Models\Qualification;
use Illuminate\Database\Seeder;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'WAEC',
            'NECO',
            'NABTEB',
            'HND',
            'OND',
            'PhD',
            'MBBS',
            'MSc',
            'MPhil',
            'MA',
            'B TECH',
            'BA',
            'BSc',
            'TRADE TEST',
            'DSc',
            'DPharm',
            'D Litt',
            'DDS',
            'DA',
            'MMed',
            'MEng',
            'BArch',
            'LLM',
            'LLB',
            'MBA',
            'BEd',
            'BPharm',
            'BVSc',
            'DVM',
            'BDS',
            'BEng',
            'BTech',
            'BBA',
            'BCom',
            'BFA',
            'BPE',
            'BSc (Ed)',
            'PGD',
            'PGDE',
            'Other',
        ];

        $now = now();
        $rows = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $rows[] = [
                'name' => $name,
                'name_normalized' => Qualification::normalizeName($name),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Qualification::query()->upsert(
            $rows,
            uniqueBy: ['name_normalized'],
            update: ['name', 'is_active', 'updated_at']
        );
    }
}

