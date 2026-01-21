<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateStatusRequest;
use App\Models\Application;
use App\Models\StatusHistory;
use App\Events\ApplicationApproved;
use App\Events\ApplicationRejected;
use App\Jobs\SendApprovalNotification;
use App\Services\Application\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatusController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService
    ) {}

    /**
     * Update application status from ASP
     * 
     * POST /api/v1/update-status
     * 
     * Body:
     * {
     *   "application_id": 123,
     *   "status": "approved",
     *   "student_code": "STU2024001",  // Only when approved
     *   "notes": "Valid application",
     *   "changed_by": "admin@school.edu"
     * }
     */
    public function update(UpdateStatusRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $application = Application::findOrFail($validated['application_id']);
            $oldStatus = $application->status;

            // Update status
            $application->status = $validated['status'];
            $application->admin_notes = $validated['notes'] ?? null;

            // If approved, assign student code
            if ($validated['status'] === 'approved') {
                $application->approved_at = now();
                $application->approved_by = $validated['changed_by'] ?? 'system';
                
                if (isset($validated['student_code'])) {
                    $application->student->update([
                        'student_code' => $validated['student_code']
                    ]);
                }
            }

            // If rejected or request_info
            if (in_array($validated['status'], ['rejected', 'request_info'])) {
                $application->rejection_reason = $validated['notes'] ?? null;
            }

            $application->save();

            // Save change history
            StatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => $validated['status'],
                'changed_by' => $validated['changed_by'] ?? 'ASP System',
                'notes' => $validated['notes'] ?? null,
                'source' => 'asp'
            ]);

            DB::commit();

            // Dispatch events and jobs
            match($validated['status']) {
                'approved' => event(new ApplicationApproved($application)),
                'rejected' => event(new ApplicationRejected($application)),
                'request_info' => SendApprovalNotification::dispatch($application, 'request_info'),
                default => null
            };

            Log::info('Application status updated via API', [
                'application_id' => $application->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'changed_by' => $validated['changed_by'] ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'application_id' => $application->id,
                    'old_status' => $oldStatus,
                    'new_status' => $application->status,
                    'student_code' => $application->student->student_code
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update application status', [
                'application_id' => $validated['application_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update
     * 
     * POST /api/v1/bulk-update-status
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applications' => 'required|array|min:1|max:50',
            'applications.*.application_id' => 'required|exists:applications,id',
            'applications.*.status' => 'required|in:approved,rejected,request_info',
            'applications.*.student_code' => 'nullable|string',
            'applications.*.notes' => 'nullable|string',
            'changed_by' => 'required|string'
        ]);

        $results = [];

        foreach ($validated['applications'] as $item) {
            try {
                $item['changed_by'] = $validated['changed_by'];
                // Manually validate and process
                $updateRequest = new UpdateStatusRequest();
                $updateRequest->merge($item);
                $updateRequest->setContainer(app());
                $updateRequest->validateResolved();

                $this->update($updateRequest);
                $results[] = [
                    'application_id' => $item['application_id'],
                    'success' => true
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'application_id' => $item['application_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
}
