<?php

declare(strict_types=1);

namespace App\Services\Student;

use App\Models\StudentAcademicRecord;

class DatabaseStudentInformationService implements StudentInformationServiceInterface
{
    /**
     * Get the student's grades from the database cache.
     */
    public function getGrades(string $studentCode): array
    {
        $record = StudentAcademicRecord::where('student_code', $studentCode)->first();

        return $record->grades ?? [];
    }

    /**
     * Get the student's class schedule/timetable from the database cache.
     */
    public function getSchedule(string $studentCode): array
    {
        $record = StudentAcademicRecord::where('student_code', $studentCode)->first();

        return $record->schedule ?? [];
    }

    /**
     * Get the student's fee summary and transaction history from the database cache.
     */
    public function getFees(string $studentCode): array
    {
        $record = StudentAcademicRecord::where('student_code', $studentCode)->first();

        return $record->fees ?? [
            'balance' => 0,
            'currency' => 'KES',
            'status' => 'Unknown',
            'invoice_history' => [],
        ];
    }
}
