<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParentDetailsRequest;
use App\Http\Requests\PersonalDetailsRequest;
use App\Http\Requests\ProgramSelectionRequest;
use App\Models\Application;
use App\Models\Program;
use App\Services\Application\ApplicationService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationFormController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService,
        private DocumentService $documentService
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
            return redirect()->route('application.program', $application);
        }

        return back()->with('status', 'parent-updated');
    }

    public function program(Application $application): View
    {
        $this->authorize('update', $application);

        $programs = Program::where('is_active', true)->get();

        return view('application.program', [
            'application' => $application,
            'programs' => $programs,
        ]);
    }

    public function updateProgram(ProgramSelectionRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $this->applicationService->updateProgram($application, $request->validated());

        if ($request->input('action') === 'next') {
            return redirect()->route('application.documents', $application);
        }

        return back()->with('status', 'program-updated');
    }

    public function documents(Application $application): View
    {
        $this->authorize('update', $application);

        $documents = $this->applicationService->getDocuments($application);

        return view('application.documents', [
            'application' => $application,
            'documents' => $documents,
        ]);
    }

    public function updateDocuments(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        // Handle file uploads
        if ($request->hasFile('national_id')) {
            $request->validate(['national_id' => 'file|mimes:jpeg,png,pdf|max:5120']);
            $this->documentService->store($application, $request->file('national_id'), 'national_id');
        }

        if ($request->hasFile('transcript')) {
            $request->validate(['transcript' => 'file|mimes:jpeg,png,pdf|max:5120']);
            $this->documentService->store($application, $request->file('transcript'), 'transcript');
        }

        $action = $request->input('action');

        if ($action === 'next') {
            try {
                // Validate documents and mark step 4 complete
                $this->applicationService->saveStep($application, 4, [], true);
                return redirect()->route('application.payment', $application);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => $e->getMessage()]);
            }
        }

        // Save Draft
        $this->applicationService->saveStep($application, 4, [], false);
        return back()->with('status', 'documents-updated');
    }

    public function payment(Application $application): View
    {
        $this->authorize('update', $application);

        return view('application.payment', [
            'application' => $application,
            'payment' => $application->payment()->latest()->first(), // Get latest payment
        ]);
    }

    public function submit(Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        try {
            $this->applicationService->submit($application);
            return redirect()->route('dashboard')->with('status', 'application-submitted');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
