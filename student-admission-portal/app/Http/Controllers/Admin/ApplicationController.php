<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $query = Application::with('student')->latest();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('middle_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('admin.applications.index', compact('applications'));
    }

    public function show(Application $application)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $application->load(['student.parentInfo', 'documents', 'payment', 'statusHistories']);

        return view('admin.applications.show', compact('application'));
    }

    public function approve(Application $application)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // Allow admins to approve from any status except draft
        if ($application->status === 'draft') {
            return back()->with('error', 'Cannot approve a draft application.');
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
