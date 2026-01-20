<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\ApplicationStep;
use App\Models\Student;
// use App\Enums\ApplicationStatus;
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
     * Tạo application mới
     */
    public function create(int $userId): Application
    {
        return DB::transaction(function () use ($userId) {
            // Tạo student record nếu chưa có
            $student = Student::firstOrCreate(
                ['user_id' => $userId],
                [
                    'first_name' => '',
                    'last_name' => '',
                    'date_of_birth' => now(),
                    'gender' => 'other',
                    'address' => '',
                    'city' => ''
                ]
            );

            // Tạo application
            $application = Application::create([
                'student_id' => $student->id,
                'program_id' => null, // Placeholder, requires DB change to be nullable or set later
                'block_id' => null, // Placeholder
                'application_number' => $this->generateApplicationNumber(),
                'status' => 'draft',
                'current_step' => 1,
                'total_steps' => count($this->stepConfig)
            ]);

            // Tạo các bước
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
     * Lưu dữ liệu một bước
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

        // Cập nhật thông tin student nếu cần
        $this->updateStudentFromStep($application->student, $stepNumber, $data);

        // Cập nhật current_step
        if ($stepNumber >= $application->current_step) {
            $application->update(['current_step' => min($stepNumber + 1, $application->total_steps)]);
        }

        return $step->fresh();
    }

    /**
     * Nộp hồ sơ
     */
    public function submit(Application $application): Application
    {
        // Kiểm tra đã hoàn thành tất cả các bước
        $incompleteSteps = $application->steps()->where('is_completed', false)->count();
        
        if ($incompleteSteps > 0) {
            throw new \Exception("Please complete all steps before submitting");
        }

        // Kiểm tra đã thanh toán
        if (!$application->payment || $application->payment->status !== 'submitted') {
            throw new \Exception("Please submit payment proof before submitting application");
        }

        $application->update([
            'status' => 'pending_approval',
            'submitted_at' => now()
        ]);

        // Dispatch event
        // event(new \App\Events\ApplicationSubmitted($application));

        return $application->fresh();
    }

    /**
     * Tạo mã hồ sơ
     */
    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $random = strtoupper(Str::random(6));
        $number = "APP-{$year}-{$random}";

        // Đảm bảo unique
        while (Application::where('application_number', $number)->exists()) {
            $random = strtoupper(Str::random(6));
            $number = "APP-{$year}-{$random}";
        }

        return $number;
    }

    /**
     * Cập nhật thông tin student từ step data
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
