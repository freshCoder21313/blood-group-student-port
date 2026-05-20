<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\Application\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentSyncController extends Controller
{
    public function __construct(
        private ApplicationService $service
    ) {}

    /**
     * GET /api/v1/students?status=pending
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $query = Application::query()
            ->with(['student', 'documents', 'payment']);

        if ($status === 'pending') {
            $query->where('status', 'pending_approval');
        } elseif ($status) {
            $query->where('status', $status);
        }

        $applications = $query->get()->map(function ($app) {
            // Transform documents to include full URLs
            $app->documents->transform(function ($doc) {
                // Ensure the path is absolute/accessible
                // Using url() from the Storage facade, assuming 'public' disk
                // Use default disk or specific url generation logic if disk is abstract
                $doc->full_url = asset('storage/'.$doc->path);

                return $doc;
            });

            return $app;
        });

        return response()->json($applications);
    }

    /**
     * POST /api/v1/update-status
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'status' => ['required', Rule::in(['approved', 'rejected', 'request_info'])],
            'note' => 'nullable|string',
            'student_code' => 'nullable|string|required_if:status,approved',
        ]);

        try {
            $application = Application::findOrFail($validated['application_id']);

            $this->service->updateStatus(
                $application,
                $validated['status'],
                $validated['note'] ?? null,
                'ASP',
                $validated['student_code'] ?? null
            );

            // If request_info, specific logic is handled by the Event listener (ApplicationStatusChanged)
            // which usually sends an email.

            return response()->json([
                'message' => 'Status updated successfully',
                'application_id' => $application->id,
                'status' => $validated['status'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/students/{student_code}/academic-records
     */
    public function updateAcademicRecords(Request $request, string $studentCode): JsonResponse
    {
        $validated = $request->validate([
            'grades' => 'nullable|array',
            'schedule' => 'nullable|array',
            'fees' => 'nullable|array',
        ]);

        try {
            // Only update fields present in the payload to avoid overwriting existing data with null
            $updateData = array_filter([
                'grades' => $validated['grades'] ?? null,
                'schedule' => $validated['schedule'] ?? null,
                'fees' => $validated['fees'] ?? null,
            ], fn ($value) => $value !== null);

            $record = \App\Models\StudentAcademicRecord::updateOrCreate(
                ['student_code' => $studentCode],
                $updateData
            );

            return response()->json([
                'success' => true,
                'message' => 'Academic records updated successfully',
                'student_code' => $studentCode,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
