<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AspSyncController extends Controller
{
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
        $statusCode = 200;
        $errorMessage = null;

        try {
            $applications = Application::query()
                ->where('status', 'pending_approval')
                ->with(['student', 'documents'])
                ->paginate(50);

            return ApplicationResource::collection($applications);
        } catch (\Throwable $e) {
            $statusCode = 500;
            $errorMessage = $e->getMessage();
            throw $e;
        } finally {
            \App\Models\ApiLog::create([
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'status_code' => $statusCode,
                'ip_address' => $request->ip(),
                'error_message' => $errorMessage,
            ]);
        }
    }
}
