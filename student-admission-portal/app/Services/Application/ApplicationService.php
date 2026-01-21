<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Models\Application;
use App\Models\ApplicationStep;
use App\Models\Student;
use App\Models\StatusHistory;
use App\Events\ApplicationSubmitted;
use App\Events\ApplicationStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
    public function saveStep(Application $application, int $stepNumber, array $data, bool $isCompleted = true): ApplicationStep
    {
        $step = ApplicationStep::where('application_id', $application->id)
                               ->where('step_number', $stepNumber)
                               ->firstOrFail();

        if ($isCompleted && $stepNumber === 4) {
            $this->validateDocuments($application);
        }

        $step->update([
            'data' => array_merge($step->data ?? [], $data),
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null
        ]);

        // Update student info if needed
        $this->updateStudentFromStep($application->student, $stepNumber, $data);

        // Update current_step ONLY if completed
        if ($isCompleted && $stepNumber >= $application->current_step) {
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
        if (!$application->payment || !in_array($application->payment->status, ['submitted', 'completed', 'pending_verification', 'verified'])) {
            throw new \Exception("Please submit payment proof before submitting application");
        }

        // Strict Validation
        $application->load(['student.parentInfo']);
        
        $validator = Validator::make($application->toArray(), [
            'student.first_name' => 'required',
            'student.last_name' => 'required',
            'student.date_of_birth' => 'required',
            'student.gender' => 'required',
            'student.nationality' => 'required',
            'student.national_id' => 'required',
            'student.parent_info.guardian_name' => 'required',
            'student.parent_info.guardian_phone' => 'required',
            'program_id' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $application->update([
            'status' => 'pending_approval',
            'submitted_at' => now()
        ]);

        ApplicationSubmitted::dispatch($application);

        return $application->fresh();
    }

    /**
     * Update application status (from ASP sync)
     */
    public function updateStatus(Application $application, string $status, ?string $notes = null, string $source = 'system'): Application
    {
        if ($application->status !== 'pending_approval') {
             throw new \Exception("Application status cannot be updated. Current status: {$application->status}");
        }

        return DB::transaction(function () use ($application, $status, $notes, $source) {
            $oldStatus = $application->status;

            // Update application
            $application->update([
                'status' => $status
            ]);

            // Create history
            StatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => $status,
                'source' => $source,
                'changed_by' => 'system',
                'notes' => $notes
            ]);

            // Fire event
            ApplicationStatusChanged::dispatch($application, $oldStatus, $status);

            return $application;
        });
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
                $this->updatePersonalDetails($student, $data);
                break;

            case 2: // Parent Info
                $this->updateParentDetails($student, $data);
                break;
        }
    }

    /**
     * Update student personal details
     */
    public function updatePersonalDetails(Student $student, array $data): void
    {
        $fields = [
            'first_name', 'middle_name', 'last_name', 'date_of_birth',
            'gender', 'nationality', 'national_id', 'passport_number',
            'address', 'city', 'county', 'postal_code'
        ];

        $updateData = array_intersect_key($data, array_flip($fields));

        if (!empty($updateData)) {
            $student->update($updateData);
        }
    }

    /**
     * Update student parent details
     */
    public function updateParentDetails(Student $student, array $data): void
    {
        $fields = [
            'relationship', 'guardian_name', 'guardian_phone', 'guardian_email'
        ];

        $updateData = array_intersect_key($data, array_flip($fields));

        $student->parentInfo()->updateOrCreate(
            ['student_id' => $student->id],
            $updateData
        );
    }

    /**
     * Update application program
     */
    public function updateProgram(Application $application, array $data): void
    {
        $programId = $data['program_id'] ?? null;

        DB::transaction(function () use ($application, $programId) {
            $application->update(['program_id' => $programId]);
            
            // Update step 3 (Program Selection)
            // Only mark complete if program_id is selected
            $this->saveStep($application, 3, ['program_id' => $programId], !is_null($programId));
        });
    }

    /**
     * Get application documents
     */
    public function getDocuments(Application $application)
    {
        return $application->documents;
    }

    /**
     * Validate documents
     */
    private function validateDocuments(Application $application): void
    {
        $documents = $this->getDocuments($application);
        $types = $documents->pluck('type')->toArray();
        
        $required = ['national_id', 'transcript'];
        
        foreach ($required as $type) {
            if (!in_array($type, $types)) {
                throw new \Exception("Required documents missing: $type");
            }
        }
    }

    /**
     * Get admission fee amount
     */
    public function getAdmissionFee(): float
    {
        return (float) config('admission.payment.amount', 1000);
    }
}
