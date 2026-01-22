<?php

namespace Database\Seeders;

use App\Models\AcademicBlock;
use Illuminate\Database\Seeder;

class AcademicBlockSeeder extends Seeder
{
    public function run(): void
    {
        AcademicBlock::firstOrCreate([
            'name' => 'Sep 2024 Intake',
            'year' => 2024,
            'intake' => 'September',
        ], [
            'start_date' => '2024-09-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);
        
        AcademicBlock::firstOrCreate([
            'name' => 'Jan 2025 Intake',
            'year' => 2025,
            'intake' => 'January',
        ], [
            'start_date' => '2025-01-01',
            'end_date' => '2025-04-30',
            'is_active' => true,
        ]);

        AcademicBlock::firstOrCreate([
            'name' => 'May 2025 Intake',
            'year' => 2025,
            'intake' => 'May',
        ], [
            'start_date' => '2025-05-01',
            'end_date' => '2025-08-31',
            'is_active' => true,
        ]);

        AcademicBlock::firstOrCreate([
            'name' => 'Sep 2025 Intake',
            'year' => 2025,
            'intake' => 'September',
        ], [
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);
    }
}
