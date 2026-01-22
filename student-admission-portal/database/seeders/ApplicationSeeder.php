<?php

namespace Database\Seeders;

use App\Models\AcademicBlock;
use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have programs and blocks
        $programs = Program::all();
        $blocks = AcademicBlock::all();

        if ($programs->isEmpty() || $blocks->isEmpty()) {
            return;
        }

        // 1. Draft Application (Step 1)
        $this->createApplication(
            'draft@example.com',
            'Draft User',
            'draft',
            1,
            $programs->random(),
            $blocks->random()
        );

        // 2. Draft Application (Step 2 - Program Selected)
        $this->createApplication(
            'draft2@example.com',
            'Draft Step 2',
            'draft',
            2,
            $programs->random(),
            $blocks->random()
        );

        // 3. Submitted Application (Pending Approval)
        $app = $this->createApplication(
            'submitted@example.com',
            'Submitted User',
            'pending_approval',
            5,
            $programs->random(),
            $blocks->random()
        );
        $app->submitted_at = now();
        $app->save();

        // 4. Approved Application
        $app = $this->createApplication(
            'approved@example.com',
            'Approved User',
            'approved',
            5,
            $programs->random(),
            $blocks->random()
        );
        $app->submitted_at = now()->subDays(2);
        $app->approved_at = now()->subDay();
        $app->approved_by = 1; // Assuming admin ID 1
        $app->save();

        // 5. Rejected Application
        $app = $this->createApplication(
            'rejected@example.com',
            'Rejected User',
            'rejected',
            5,
            $programs->random(),
            $blocks->random()
        );
        $app->submitted_at = now()->subDays(3);
        $app->approved_at = now()->subDays(2); // Actually rejected time
        $app->approved_by = 1;
        $app->rejection_reason = 'Documents verification failed. Please re-upload clearer copies.';
        $app->save();

        // 6. Enrolled Student (Status: approved, but has student code)
        $app = $this->createApplication(
            'student@example.com',
            'Enrolled Student',
            'approved',
            5,
            $programs->random(),
            $blocks->random()
        );
        $app->submitted_at = now()->subDays(10);
        $app->approved_at = now()->subDays(5);
        $app->student->student_code = 'ST-' . now()->year . '-' . Str::upper(Str::random(5));
        $app->student->save();
        $app->save();
    }

    private function createApplication($email, $name, $status, $step, $program, $block)
    {
        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::factory()->create([
                'email' => $email,
                // Password is 'password' by default in factory
            ]);
        }

        // Check if student exists
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
             $student = Student::factory()->create([
                'user_id' => $user->id,
                'first_name' => explode(' ', $name)[0],
                'last_name' => explode(' ', $name)[1] ?? 'User',
            ]);
        }
       
        return Application::firstOrCreate(
            ['student_id' => $student->id],
            [
                'program_id' => $program->id,
                'block_id' => $block->id,
                'application_number' => 'APP-' . strtoupper(Str::random(8)),
                'status' => $status,
                'current_step' => $step,
                'total_steps' => 5,
            ]
        );
    }
}
