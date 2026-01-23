<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Models\Application;
use App\Models\ApplicationStep;
use App\Models\Student;
use App\Events\ApplicationSubmitted;
use App\Events\ApplicationStatusChanged;
use App\Models\StatusHistory;
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
            $student = Student::firstOrCreate(
                ['user_id' => $userId],
                ['first_name' => null] // Allow partial creation
            );

            $application = Application::create([
                'student_id' => $student->id,
                'application_number' => $this->generateApplicationNumber(),
                'status' => 'draft',
                'current_step' => 1,
                'total_steps' => count($this->stepConfig),
                'payment_status' => 'unpaid',
            ]);

            // Initialize steps
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
     * Save step data and sync to main tables
     */
    public function saveStep(Application $application, int $stepNumber, array $data, bool $isCompleted = true): ApplicationStep
    {
        return DB::transaction(function () use ($application, $stepNumber, $data, $isCompleted) {
            $step = ApplicationStep::where('application_id', $application->id)
                                   ->where('step_number', $stepNumber)
                                   ->firstOrFail();

            // 1. Sync Data to Main Tables
            if ($stepNumber === 1) {
                $this->syncStudentData($application->student, $data);
            } elseif ($stepNumber === 2) {
                $this->syncParentData($application->student, $data);
            } elseif ($stepNumber === 3) {
                 if (isset($data['program_id'])) {
                    $application->update(['program_id' => $data['program_id']]);
                 }
            } elseif ($isCompleted && $stepNumber === 4) {
                 // Documents are usually handled via DocumentService directly, 
                 // but we can validate them here if needed.
                 // The actual file upload sync happens in the controller via DocumentService.
                 // Here we just mark progress.
                 $this->validateDocuments($application);
            }

            // 2. Update Step Record
            $step->update([
                'data' => array_merge($step->data ?? [], $data),
                'is_completed' => $isCompleted, 
                'completed_at' => $isCompleted ? now() : null,
            ]);

            // 3. Update Current Step (Advance if moving forward)
            if ($isCompleted && $stepNumber >= $application->current_step && $stepNumber < $application->total_steps) {
                $application->update(['current_step' => $stepNumber + 1]);
            }

            return $step;
        });
    }

    /**
     * Submit application
     */
    public function submitApplication(Application $application): Application
    {
        // 1. Validate Payment
        // Check both the payment relationship and the status column for robustness
        $payment = $application->payment;
        $isPaid = ($application->payment_status === 'paid') || 
                  ($payment && in_array($payment->status, ['completed', 'verified', 'pending_verification']));

        if (!$isPaid) {
            throw new \Exception("Please submit payment proof");
        }

        // 2. Validate All Steps Completed
        $incompleteSteps = $application->steps()
            ->where('is_completed', false)
            ->where('step_number', '<=', $application->total_steps) // Ensure we check all required steps
            ->exists();
        
        if ($incompleteSteps) {
             throw new \Exception("Please complete all application steps.");
        }

        // Strict Validation (Restored)
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

        return DB::transaction(function () use ($application) {
            $oldStatus = $application->status;

            $application->update([
                'status' => 'pending_approval',
                'submitted_at' => now()
            ]);

            // Log History
            StatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => 'pending_approval',
                'source' => 'system',
                'changed_by' => 'student',
                'comment' => 'Application submitted'
            ]);

            // 3. Trigger Notification/Event
            ApplicationSubmitted::dispatch($application);
            ApplicationStatusChanged::dispatch($application, $oldStatus, 'pending_approval');

            return $application->fresh();
        });
    }
    
    // Alias for backward compatibility if needed, but intended to be replaced
    public function submit(Application $application): Application 
    {
        return $this->submitApplication($application);
    }

    /**
     * Update application status (from ASP sync)
     */
    public function updateStatus(Application $application, string $status, ?string $notes = null, string $source = 'system', ?string $studentCode = null): Application
    {
        if ($application->status !== 'pending_approval') {
             throw new \Exception("Application status cannot be updated. Current status: {$application->status}");
        }

        return DB::transaction(function () use ($application, $status, $notes, $source, $studentCode) {
            $oldStatus = $application->status;

            // Update application
            $application->update([
                'status' => $status
            ]);
            
            // Update Student Code if Approved
            if ($status === 'approved' && $studentCode) {
                $application->student->update(['student_code' => $studentCode]);
            }

            // Create history
            StatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => $status,
                'source' => $source,
                'changed_by' => 'system',
                'comment' => $notes
            ]);

            // Fire event
            ApplicationStatusChanged::dispatch($application, $oldStatus, $status);

            return $application;
        });
    }

    public function getDocuments(Application $application)
    {
        return $application->documents;
    }

    public function updateProgram(Application $application, array $data): void
    {
        $this->saveStep($application, 3, $data);
    }

    private function syncStudentData(Student $student, array $data): void
    {
        $fillable = $student->getFillable();
        // filter data to only fillable columns
        $updateData = array_intersect_key($data, array_flip($fillable));
        if (!empty($updateData)) {
            $student->update($updateData);
        }
    }

    private function syncParentData(Student $student, array $data): void
    {
        $parentInfo = $student->parentInfo ?? new \App\Models\ParentInfo(['student_id' => $student->id]);
        $fillable = $parentInfo->getFillable();
        $updateData = array_intersect_key($data, array_flip($fillable));
        
        if (!empty($updateData)) {
            $student->parentInfo()->updateOrCreate(
                ['student_id' => $student->id],
                $updateData
            );
        }
    }

    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        do {
            $random = strtoupper(Str::random(6));
            $number = "APP-{$year}-{$random}";
        } while (Application::where('application_number', $number)->exists());

        return $number;
    }

    private function validateDocuments(Application $application): void
    {
         $documents = $application->documents->pluck('type')->toArray();
         $required = ['national_id', 'transcript'];
         
         if (count(array_intersect($required, $documents)) !== count($required)) {
             throw new \Exception("Missing required documents.");
         }
    }

    public function getAdmissionFee(): float
    {
        return (float) config('admission.payment.amount', 1000);
    }
}
