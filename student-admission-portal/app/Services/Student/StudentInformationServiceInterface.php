<?php

declare(strict_types=1);

namespace App\Services\Student;

interface StudentInformationServiceInterface
{
    public function getGrades(string $studentCode): array;

    public function getSchedule(string $studentCode): array;

    public function getFees(string $studentCode): array;
}
