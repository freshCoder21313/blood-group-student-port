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
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
