<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function statusChanged(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required|integer|exists:applications,id',
            'status' => 'required|string|in:approved,rejected,pending_approval,request_info',
            'reason' => 'nullable|string',
            'processed_by' => 'nullable|string'
        ]);

        $application = \App\Models\Application::findOrFail($validated['application_id']);
        
        // Update status
        $application->update([
            'status' => $validated['status']
        ]);

        // Log history
        \App\Models\StatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $application->getOriginal('status') ?? 'unknown',
            'to_status' => $validated['status'],
            'note' => $validated['reason'] ?? 'Updated via ASP Webhook',
            'created_by' => $validated['processed_by'] ?? 'System (ASP)'
        ]);

        // Trigger notification if needed
        if (in_array($validated['status'], ['approved', 'rejected'])) {
            \App\Jobs\SendApprovalNotification::dispatch($application, $validated['status']);
        }

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function gradeUpdated(Request $request)
    {
        $validated = $request->validate([
            'student_code' => 'required|string',
        ]);

        // Clear cache for this student's grades
        $cacheKey = "grades:{$validated['student_code']}";
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        return response()->json(['success' => true, 'message' => 'Grade cache cleared']);
    }
}
