<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Programs and Blocks first
        $this->call([
            ProgramSeeder::class,
            AcademicBlockSeeder::class,
        ]);

        $programs = \App\Models\Program::all();
        $blocks = \App\Models\AcademicBlock::all();

        if ($programs->isEmpty() || $blocks->isEmpty()) {
            throw new \Exception("Programs or Academic Blocks not seeded. Check seeders.");
        }

        // 2. Create Admins
        User::firstOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name' => 'System Admin',
                'phone' => '0700000000',
                'password' => bcrypt('password'),
                'status' => 'active',
                'role' => 'admin'
            ]
        );

        // 3. Create Admitted Students (Status active + student_code)
        for ($i = 1; $i <= 10; $i++) {
            $user = User::factory()->create([
                'name' => "Admitted Student $i",
                'email' => "student$i@example.com",
                'status' => 'active',
                'role' => 'student'
            ]);

            $student = \App\Models\Student::factory()->create([
                'user_id' => $user->id,
                'student_code' => "STD-2026-" . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'first_name' => "Student",
                'last_name' => (string) $i,
            ]);

            \App\Models\Application::factory()->create([
                'student_id' => $student->id,
                'status' => 'approved',
                'student_code' => $student->student_code,
                'program_id' => $programs->random()->id,
                'block_id' => $blocks->random()->id,
            ]);
        }

        // 4. Create Applicants with various statuses
        $statuses = ['pending_approval', 'request_info', 'rejected', 'draft'];
        foreach ($statuses as $status) {
            for ($i = 1; $i <= 5; $i++) {
                $user = User::factory()->create([
                    'name' => "Applicant " . ucfirst(str_replace('_', ' ', $status)) . " $i",
                    'email' => "{$status}{$i}@example.com",
                    'status' => 'new',
                    'role' => 'student'
                ]);

                $student = \App\Models\Student::factory()->create(['user_id' => $user->id]);

                \App\Models\Application::factory()->create([
                    'student_id' => $student->id,
                    'status' => $status,
                    'program_id' => $programs->random()->id,
                    'block_id' => $blocks->random()->id,
                ]);
            }
        }
    }
}
