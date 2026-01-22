<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateApplicationStatusRequest;
use App\Http\Resources\V1\ApplicationResource;
use App\Models\Application;
use App\Services\Application\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AspSyncController extends Controller
{
    protected ApplicationService $service;

    public function __construct(ApplicationService $service)
    {
        $this->service = $service;
    }
    /**
     * Ping endpoint to verify connectivity and auth.
     */
    public function ping(): JsonResponse
    {
        return response()->json(['message' => 'pong']);
    }

    /**
     * Get list of pending applications for sync.
     */
    public function pending(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $applications = Application::query()
            ->where('status', 'pending_approval')
            ->with(['student', 'documents'])
            ->paginate(50);

        return ApplicationResource::collection($applications);
    }

    /**
     * Update application status.
     */
    public function updateStatus(UpdateApplicationStatusRequest $request): JsonResponse
    {
        try {
            $application = Application::findOrFail($request->validated('application_id'));

            $updatedApplication = $this->service->updateStatus(
                $application,
                $request->validated('status'),
                $request->validated('comment'), // notes
                'ASP' // source
            );

            if ($request->validated('student_code')) {
                $updatedApplication->update(['student_code' => $request->validated('student_code')]);
            }

            return response()->json([
                'message' => 'Status updated',
                'data' => new ApplicationResource($updatedApplication)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
