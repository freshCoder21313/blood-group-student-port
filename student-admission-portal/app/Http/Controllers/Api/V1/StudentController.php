<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentCollection;
use App\Models\Application;
use App\Models\Student;
use App\Services\Integration\AspApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function __construct(
        private AspApiService $aspService
    ) {}

    /**
     * Lấy danh sách sinh viên theo trạng thái
     * 
     * GET /api/v1/students?status=pending_approval&page=1&per_page=50
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|in:draft,pending_payment,pending_approval,request_info,approved,rejected',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Application::with([
            'student',
            'student.parentInfo',
            'program',
            'block',
            'documents',
            'payment'
        ]);

        // Filter by status
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filter by date range
        if (isset($validated['from_date'])) {
            $query->where('submitted_at', '>=', $validated['from_date']);
        }
        if (isset($validated['to_date'])) {
            $query->where('submitted_at', '<=', $validated['to_date']);
        }

        $perPage = $validated['per_page'] ?? 50;
        $applications = $query->orderBy('submitted_at', 'desc')
                             ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($applications),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ]
        ]);
    }

    /**
     * Lấy chi tiết một hồ sơ
     * 
     * GET /api/v1/students/{id}
     */
    public function show(int $id): JsonResponse
    {
        $application = Application::with([
            'student',
            'student.parentInfo',
            'program',
            'block',
            'documents',
            'payment',
            'steps',
            'statusHistories'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new StudentResource($application)
        ]);
    }

    /**
     * Lấy điểm sinh viên từ ASP
     * 
     * GET /api/v1/students/{student_code}/grades
     */
    public function grades(string $studentCode): JsonResponse
    {
        $student = Student::where('student_code', $studentCode)
                         ->whereHas('application', fn($q) => $q->where('status', 'approved'))
                         ->firstOrFail();

        // Gọi API ASP để lấy điểm
        $grades = $this->aspService->getStudentGrades($studentCode);

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    /**
     * Lấy thời khóa biểu từ ASP
     * 
     * GET /api/v1/students/{student_code}/timetable
     */
    public function timetable(string $studentCode): JsonResponse
    {
        $student = Student::where('student_code', $studentCode)
                         ->whereHas('application', fn($q) => $q->where('status', 'approved'))
                         ->firstOrFail();

        $timetable = $this->aspService->getStudentTimetable($studentCode);

        return response()->json([
            'success' => true,
            'data' => $timetable
        ]);
    }

    /**
     * Lấy thông tin học phí từ ASP
     * 
     * GET /api/v1/students/{student_code}/fees
     */
    public function fees(string $studentCode): JsonResponse
    {
        $student = Student::where('student_code', $studentCode)
                         ->whereHas('application', fn($q) => $q->where('status', 'approved'))
                         ->firstOrFail();

        $fees = $this->aspService->getStudentFees($studentCode);

        return response()->json([
            'success' => true,
            'data' => $fees
        ]);
    }
}
