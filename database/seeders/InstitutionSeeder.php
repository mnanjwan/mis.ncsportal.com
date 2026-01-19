<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            // Nigeria + nearby (starter list used by the UI previously)
            'University of Lagos (UNILAG)',
            'University of Ibadan (UI)',
            'Ahmadu Bello University (ABU)',
            'University of Nigeria, Nsukka (UNN)',
            'Obafemi Awolowo University (OAU)',
            'University of Benin (UNIBEN)',
            'University of Ilorin (UNILORIN)',
            'University of Port Harcourt (UNIPORT)',
            'University of Calabar (UNICAL)',
            'University of Jos (UNIJOS)',
            'University of Maiduguri (UNIMAID)',
            'University of Uyo (UNIUYO)',
            'Nnamdi Azikiwe University (UNIZIK)',
            'Federal University of Technology, Akure (FUTA)',
            'Federal University of Technology, Minna (FUTMINNA)',
            'Federal University of Technology, Owerri (FUTO)',
            'Federal University of Agriculture, Abeokuta (FUNAAB)',
            'Federal University of Agriculture, Makurdi (FUAM)',
            'Federal University of Petroleum Resources, Effurun (FUPRE)',
            'Lagos State University (LASU)',
            'Rivers State University (RSU)',
            'Delta State University (DELSU)',
            'Enugu State University of Science and Technology (ESUT)',
            'Abia State University (ABSU)',
            'Imo State University (IMSU)',
            'Anambra State University (ANSU)',
            'Bayelsa Medical University (BMU)',
            'Benue State University (BSU)',
            'Cross River University of Technology (CRUTECH)',
            'Ebonyi State University (EBSU)',
            'Ekiti State University (EKSU)',
            'Kaduna State University (KASU)',
            'Kano University of Science and Technology (KUST)',
            'Kebbi State University of Science and Technology (KSUSTA)',
            'Kwara State University (KWASU)',
            'Nasarawa State University (NSUK)',
            'Ondo State University of Science and Technology (OSUSTECH)',
            'Osun State University (UNIOSUN)',
            'Plateau State University (PLASU)',
            'Sokoto State University (SSU)',
            'Taraba State University (TSU)',
            'Yobe State University (YSU)',
            'Zamfara State University (ZASU)',
            'Covenant University',
            'Babcock University',
            'Afe Babalola University (ABUAD)',
            'American University of Nigeria (AUN)',
            'Bells University of Technology',
            'Benson Idahosa University',
            'Bingham University',
            'Bowen University',
            'Caleb University',
            'Caritas University',
            'Crawford University',
            'Crescent University',
            'Edwin Clark University',
            'Elizade University',
            'Evangel University',
            'Fountain University',
            'Godfrey Okoye University',
            'Gregory University',
            'Hallmark University',
            'Hezekiah University',
            'Igbinedion University',
            'Joseph Ayo Babalola University',
            'Kings University',
            'Kwararafa University',
            'Landmark University',
            'Lead City University',
            'Madonna University',
            'McPherson University',
            'Michael Okpara University of Agriculture, Umudike',
            'Nile University of Nigeria',
            'Novena University',
            'Obong University',
            'Oduduwa University',
            'Pan-Atlantic University',
            'Paul University',
            'Redeemer\'s University',
            'Rhema University',
            'Ritman University',
            'Salem University',
            'Samuel Adegboyega University',
            'Southwestern University',
            'Summit University',
            'Tansian University',
            'University of Mkar',
            'Veritas University',
            'Wesley University',
            'Western Delta University',
            // Benin Republic Universities
            'University of Abomey-Calavi (UAC)',
            'University of Parakou',
            'National University of Sciences, Technologies, Engineering, and Mathematics (UNSTIM)',
            'National University of Agriculture (UNA)',
            'African School of Economics (ASE)',
            'ESAE University (École Supérieure d\'Administration, d\'Économie, de Journalisme et des Métiers de l\'Audiovisuel)',
            'ESCAE-University, Benin',
            'ISFOP Benin University',
            'Houdegbe North American University Benin (HNAUB)',
            'Université Catholique de l\'Afrique de l\'Ouest (UCAO)',
            'Université des Sciences et Technologies du Bénin',
            'Université Africaine de Technologie et de Management',
            'Université Protestante de l\'Afrique de l\'Ouest',
            'Université Polytechnique Internationale du Bénin',
            'Université des Sciences Appliquées et du Management',
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
                'name_normalized' => Institution::normalizeName($name),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Institution::query()->upsert(
            $rows,
            uniqueBy: ['name_normalized'],
            update: ['name', 'is_active', 'updated_at']
        );
    }
}

