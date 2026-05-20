<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Student\StudentInformationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected StudentInformationServiceInterface $studentInformationService
    ) {}

    private function getStudentCode(Request $request): string
    {
        $studentCode = $request->user()->student?->student_code;

        if (! $studentCode) {
            abort(403, 'Your application has not been approved yet. Academic data is unavailable.');
        }

        return $studentCode;
    }

    public function grades(Request $request): View
    {
        try {
            $studentCode = $this->getStudentCode($request);
            $grades = $this->studentInformationService->getGrades($studentCode);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch grades', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            $grades = [];
            session()->flash('error', 'Unable to retrieve grades.');
        }

        return view('student.grades', compact('grades'));
    }

    public function schedule(Request $request): View
    {
        try {
            $studentCode = $this->getStudentCode($request);
            $schedule = $this->studentInformationService->getSchedule($studentCode);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch schedule', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            $schedule = [];
            session()->flash('error', 'Unable to retrieve schedule.');
        }

        return view('student.schedule', compact('schedule'));
    }

    public function fees(Request $request): View
    {
        try {
            $studentCode = $this->getStudentCode($request);
            $fees = $this->studentInformationService->getFees($studentCode);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch fees', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            $fees = ['balance' => 0, 'currency' => 'KES', 'status' => 'Error', 'invoice_history' => []];
            session()->flash('error', 'Unable to retrieve fee information.');
        }

        return view('student.fees', compact('fees'));
    }
}
