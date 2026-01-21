<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Models\Application;
use App\Models\ApplicationStep;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationService
{
    private array $stepConfig;

    public function __construct()
    {
        $this->stepConfig = config('admission.steps', [
            1 => ['name' => 'personal_info', 'label' => 'Personal Information'],
            2 => ['name' => 'parent_info', 'label' => 'Parent/Guardian Information'],
            3 => ['name' => 'program_selection', 'label' => 'Program Selection'],
            4 => ['name' => 'documents', 'label' => 'Document Upload'],
        ]);
    }

    /**
     * Create a new draft application
     */
    public function createDraft(int $userId): Application
    {
        return DB::transaction(function () use ($userId) {
            // Create student record if not exists
            $student = Student::firstOrCreate(
                ['user_id' => $userId],
                [
                    'first_name' => null,
                    'last_name' => null,
                    'date_of_birth' => null,
                    'gender' => 'other',
                    'address' => null,
                    'city' => null
                ]
            );

            // Create application
            $application = Application::create([
                'student_id' => $student->id,
                'program_id' => null,
                'block_id' => null,
                'application_number' => $this->generateApplicationNumber(),
                'status' => 'draft',
                'current_step' => 1,
                'total_steps' => count($this->stepConfig)
            ]);

            // Create steps
            foreach ($this->stepConfig as $stepNumber => $config) {
                ApplicationStep::create([
                    'application_id' => $application->id,
                    'step_number' => $stepNumber,
                    'step_name' => $config['name'],
                    'data' => [],
                    'is_completed' => false
                ]);
            }

            return $application;
        });
    }

    /**
     * Get current application for user
     */
    public function getCurrentApplication(int $userId): ?Application
    {
        return Application::whereHas('student', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->latest()->first();
    }

    /**
     * Save step data
     */
    public function saveStep(Application $application, int $stepNumber, array $data): ApplicationStep
    {
        $step = ApplicationStep::where('application_id', $application->id)
                               ->where('step_number', $stepNumber)
                               ->firstOrFail();

        $step->update([
            'data' => array_merge($step->data ?? [], $data),
            'is_completed' => true,
            'completed_at' => now()
        ]);

        // Update student info if needed
        $this->updateStudentFromStep($application->student, $stepNumber, $data);

        // Update current_step
        if ($stepNumber >= $application->current_step) {
            $application->update(['current_step' => min($stepNumber + 1, $application->total_steps)]);
        }

        return $step->fresh();
    }

    /**
     * Submit application
     */
    public function submit(Application $application): Application
    {
        // Check if all steps completed
        $incompleteSteps = $application->steps()->where('is_completed', false)->count();
        
        if ($incompleteSteps > 0) {
            throw new \Exception("Please complete all steps before submitting");
        }

        // Check payment
        if (!$application->payment || $application->payment->status !== 'submitted') {
            throw new \Exception("Please submit payment proof before submitting application");
        }

        $application->update([
            'status' => 'pending_approval',
            'submitted_at' => now()
        ]);

        return $application->fresh();
    }

    /**
     * Generate application number
     */
    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $random = strtoupper(Str::random(6));
        $number = "APP-{$year}-{$random}";

        // Ensure unique
        while (Application::where('application_number', $number)->exists()) {
            $random = strtoupper(Str::random(6));
            $number = "APP-{$year}-{$random}";
        }

        return $number;
    }

    /**
     * Update student info from step data
     */
    private function updateStudentFromStep(Student $student, int $stepNumber, array $data): void
    {
        switch ($stepNumber) {
            case 1: // Personal Info
                $student->update([
                    'first_name' => $data['first_name'] ?? $student->first_name,
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'] ?? $student->last_name,
                    'date_of_birth' => $data['date_of_birth'] ?? $student->date_of_birth,
                    'gender' => $data['gender'] ?? $student->gender,
                    'nationality' => $data['nationality'] ?? 'Kenya',
                    'national_id' => $data['national_id'] ?? null,
                    'address' => $data['address'] ?? $student->address,
                    'city' => $data['city'] ?? $student->city,
                    'county' => $data['county'] ?? null,
                ]);
                break;

            case 2: // Parent Info
                $student->parentInfo()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'relation_type' => $data['relation_type'] ?? 'parent',
                        'full_name' => $data['parent_name'] ?? '',
                        'phone' => $data['parent_phone'] ?? '',
                        'email' => $data['parent_email'] ?? null,
                        'occupation' => $data['parent_occupation'] ?? null,
                        'address' => $data['parent_address'] ?? '',
                    ]
                );
                break;
        }
    }
}
