<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParentDetailsRequest;
use App\Http\Requests\PersonalDetailsRequest;
use App\Models\Application;
use App\Services\Application\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationFormController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService
    ) {}

    public function personal(Application $application): View
    {
        // Simple auth check, though Policy is better.
        $this->authorize('update', $application);

        return view('application.personal', [
            'application' => $application,
            'student' => $application->student,
        ]);
    }

    public function updatePersonal(PersonalDetailsRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $this->applicationService->saveStep($application, 1, $request->validated());

        // Handle "Save & Next"
        if ($request->input('action') === 'next') {
            return redirect()->route('application.parent', $application);
        }

        return back()->with('status', 'personal-updated');
    }

    public function parent(Application $application): View
    {
        $this->authorize('update', $application);

        return view('application.parent', [
            'application' => $application,
            'student' => $application->student,
            'parentInfo' => $application->student->parentInfo,
        ]);
    }

    public function updateParent(ParentDetailsRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $this->applicationService->saveStep($application, 2, $request->validated());

        if ($request->input('action') === 'next') {
            // For now, redirect to dashboard as next story handles program selection
            return redirect()->route('dashboard')->with('status', 'parent-updated');
        }

        return back()->with('status', 'parent-updated');
    }
}
