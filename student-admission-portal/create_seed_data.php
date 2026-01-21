<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Program;
use App\Models\AcademicBlock;
use App\Models\Application;
use Illuminate\Support\Str;

echo "--- START SEEDING ---\n";

try {
    // 1. Create User
    $user = User::firstOrCreate(
        ['email' => 'parent@test.com'],
        [
            'phone' => '+84909000111', 
            'password' => bcrypt('password'),
            'status' => 'active'
        ]
    );
    echo "User ID: {$user->id}\n";

    // 2. Create Student
    $student = Student::firstOrCreate(
        ['user_id' => $user->id],
        [
            'first_name' => 'Nguyen',
            'last_name' => 'Van A',
            'date_of_birth' => '2005-01-01',
            'gender' => 'male',
            'address' => '123 Test Street',
            'city' => 'Nairobi'
        ]
    );
    echo "Student ID: {$student->id}\n";

    // 3. Create Program (Academic Program)
    $program = Program::firstOrCreate(
        ['code' => 'IT01'],
        ['name' => 'Information Technology', 'duration_years' => 4, 'type' => 'degree']
    );
    echo "Program ID: {$program->id}\n";

    // 4. Create Academic Block (Intake/Cohort)
    $block = AcademicBlock::firstOrCreate(
        ['name' => 'January 2024 Intake'],
        [
            'year' => 2024,
            'intake' => 'January',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'is_active' => true
        ]
    );
    echo "Block ID: {$block->id}\n";

    // 5. Create Application
    $app = Application::firstOrCreate(
        ['student_id' => $student->id],
        [
            'program_id' => $program->id,
            'block_id' => $block->id,
            'application_number' => 'APP-' . Str::upper(Str::random(8)),
            'status' => 'pending_payment',
            'current_step' => 4,
            'total_steps' => 4,
            'submitted_at' => now()
        ]
    );

    echo "\n>>> SUCCESS! USE THIS APPLICATION ID: {$app->id} <<<\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
