<?php

declare(strict_types=1);

namespace App\Services\Student;

class MockStudentInformationService implements StudentInformationServiceInterface
{
    public function getGrades(string $studentCode): array
    {
        return [
            ['code' => 'CS101', 'name' => 'Intro to CS', 'grade' => 'A'],
            ['code' => 'MATH101', 'name' => 'Calculus I', 'grade' => 'B+'],
        ];
    }

    public function getSchedule(string $studentCode): array
    {
        return [
            ['day' => 'Monday', 'time' => '09:00', 'course' => 'CS101', 'venue' => 'Room A'],
            ['day' => 'Tuesday', 'time' => '10:00', 'course' => 'MATH101', 'venue' => 'Room B'],
        ];
    }

    public function getFees(string $studentCode): array
    {
        return [
            'balance' => 50000,
            'currency' => 'KES',
            'status' => 'Pending',
            'invoice_history' => [
                ['invoice_id' => 'INV-001', 'amount' => 50000, 'due_date' => '2024-01-01', 'status' => 'Pending'],
                ['invoice_id' => 'INV-002', 'amount' => 10000, 'due_date' => '2023-12-01', 'status' => 'Paid'],
            ],
        ];
    }
}
