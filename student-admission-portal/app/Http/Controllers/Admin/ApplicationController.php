<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    // ...

    public function approve(Application $application)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($application->status !== 'pending_approval') {
            return back()->with('error', 'Application cannot be approved from current status.');
        }

        $application->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        ActivityLogger::log('approve_application', "Approved application {$application->application_number}", $application);

        return redirect()->route('admin.applications.index')->with('success', 'Application approved successfully.');
    }

    public function reject(Request $request, Application $application)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate(['rejection_reason' => 'required|string|max:255']);

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_at' => now(), // Decision date
            'approved_by' => auth()->id()
        ]);

        ActivityLogger::log('reject_application', "Rejected application {$application->application_number}. Reason: {$request->rejection_reason}", $application);

        return redirect()->route('admin.applications.index')->with('success', 'Application rejected.');
    }
}
