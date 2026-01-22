<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'code' => 'CS',
                'name' => 'Computer Science',
                'description' => 'Bachelor of Science in Computer Science',
                'duration' => '4 years',
                'fee' => 50000.00,
                'is_active' => true,
            ],
            [
                'code' => 'IT',
                'name' => 'Information Technology',
                'description' => 'Bachelor of Science in Information Technology',
                'duration' => '4 years',
                'fee' => 45000.00,
                'is_active' => true,
            ],
            [
                'code' => 'BBA',
                'name' => 'Business Administration',
                'description' => 'Bachelor of Business Administration',
                'duration' => '4 years',
                'fee' => 40000.00,
                'is_active' => true,
            ],
            [
                'code' => 'ENG',
                'name' => 'Civil Engineering',
                'description' => 'Bachelor of Science in Civil Engineering',
                'duration' => '5 years',
                'fee' => 55000.00,
                'is_active' => true,
            ],
            [
                'code' => 'MED',
                'name' => 'Medicine',
                'description' => 'Bachelor of Medicine and Bachelor of Surgery',
                'duration' => '6 years',
                'fee' => 70000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PSY',
                'name' => 'Psychology',
                'description' => 'Bachelor of Arts in Psychology',
                'duration' => '4 years',
                'fee' => 38000.00,
                'is_active' => true,
            ],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
