<?php

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Seeder;

class DisciplineSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Accounting',
            'Actuarial Science',
            'Agricultural Economics',
            'Agricultural Engineering',
            'Agricultural Extension',
            'Agriculture',
            'Anatomy',
            'Animal Science',
            'Architecture',
            'Banking and Finance',
            'Biochemistry',
            'Biology',
            'Biomedical Engineering',
            'Botany',
            'Business Administration',
            'Chemical Engineering',
            'Chemistry',
            'Civil Engineering',
            'Computer Engineering',
            'Computer Science',
            'Criminology',
            'Crop Science',
            'Dentistry',
            'Economics',
            'Education',
            'Electrical Engineering',
            'English Language',
            'Environmental Science',
            'Estate Management',
            'Finance',
            'Fisheries',
            'Food Science and Technology',
            'Forestry',
            'Geography',
            'Geology',
            'History',
            'Human Resource Management',
            'Industrial Chemistry',
            'Information Technology',
            'Law',
            'Library Science',
            'Linguistics',
            'Marine Engineering',
            'Marketing',
            'Mass Communication',
            'Mathematics',
            'Mechanical Engineering',
            'Medicine and Surgery',
            'Microbiology',
            'Nursing',
            'Petroleum Engineering',
            'Pharmacy',
            'Philosophy',
            'Physics',
            'Political Science',
            'Psychology',
            'Public Administration',
            'Quantity Surveying',
            'Sociology',
            'Soil Science',
            'Statistics',
            'Surveying and Geoinformatics',
            'Veterinary Medicine',
            'Zoology',
            'Agricultural Science',
            'Animal Husbandry',
            'Building Technology',
            'Business Management',
            'Chemical Science',
            'Communication Arts',
            'Computer Education',
            'Crop Production',
            'Economics and Statistics',
            'Educational Administration',
            'Educational Psychology',
            'Electronics Engineering',
            'Environmental Management',
            'Food Technology',
            'Geophysics',
            'Guidance and Counseling',
            'Health Education',
            'Home Economics',
            'Human Kinetics',
            'Industrial Mathematics',
            'Insurance',
            'International Relations',
            'Journalism',
            'Laboratory Technology',
            'Land Surveying',
            'Management',
            'Marine Science',
            'Materials Science',
            'Mechanical Engineering Technology',
            'Medical Laboratory Science',
            'Metallurgical Engineering',
            'Nutrition and Dietetics',
            'Office Technology and Management',
            'Operations Research',
            'Optometry',
            'Peace and Conflict Studies',
            'Petroleum and Gas Engineering',
            'Physics with Electronics',
            'Plant Science',
            'Project Management',
            'Public Health',
            'Pure and Applied Mathematics',
            'Radiography',
            'Real Estate Management',
            'Religious Studies',
            'Science Education',
            'Social Work',
            'Software Engineering',
            'Soil Science and Land Management',
            'Statistics and Computer Science',
            'Telecommunications Engineering',
            'Textile Technology',
            'Transport Management',
            'Urban and Regional Planning',
            'Water Resources Engineering',
            'Wildlife Management',
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
                'name_normalized' => Discipline::normalizeName($name),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Discipline::query()->upsert(
            $rows,
            uniqueBy: ['name_normalized'],
            update: ['name', 'is_active', 'updated_at']
        );
    }
}

