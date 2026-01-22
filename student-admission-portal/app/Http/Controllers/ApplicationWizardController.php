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
use Illuminate\Support\Facades\Gate;

class ApplicationWizardController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService,
        private DocumentService $documentService
    ) {}

    public function show(Application $application): View
    {
        Gate::authorize('view', $application);

        $programs = Program::where('is_active', true)->get();
        $documents = $this->applicationService->getDocuments($application);

        return view('application.wizard', [
            'application' => $application,
            'student' => $application->student,
            'parentInfo' => $application->student->parentInfo,
            'programs' => $programs,
            'documents' => $documents,
        ]);
    }

    public function save(Request $request, Application $application, int $step): RedirectResponse
    {
        Gate::authorize('update', $application);

        // Dispatch to specific save logic based on step
        return match ($step) {
            1 => $this->savePersonal($request, $application),
            2 => $this->saveParent($request, $application),
            3 => $this->saveProgram($request, $application),
            4 => $this->saveDocuments($request, $application),
            default => back()->withErrors(['error' => 'Invalid step']),
        };
    }

    private function savePersonal(Request $request, Application $application): RedirectResponse
    {
        // Hydrate request with route param to ensure rules() works correctly
        $formRequest = new PersonalDetailsRequest();
        $formRequest->setRouteResolver(function () use ($application) {
            $route = request()->route();
            $route->setParameter('application', $application);
            return $route;
        });

        $validated = $request->validate($formRequest->rules());
        
        $this->applicationService->saveStep($application, 1, $validated);

        if ($request->input('action') === 'next') {
            return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-2');
        }

        return back()->with('status', 'Step 1 Saved');
    }

    private function saveParent(Request $request, Application $application): RedirectResponse
    {
        $formRequest = new ParentDetailsRequest();
        $formRequest->setRouteResolver(function () use ($application) {
            $route = request()->route();
            $route->setParameter('application', $application);
            return $route;
        });

        $validated = $request->validate($formRequest->rules());
        
        $this->applicationService->saveStep($application, 2, $validated);

        if ($request->input('action') === 'next') {
            return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-3');
        }

        return back()->with('status', 'Step 2 Saved');
    }

    private function saveProgram(Request $request, Application $application): RedirectResponse
    {
        $formRequest = new ProgramSelectionRequest();
        $formRequest->setRouteResolver(function () use ($application) {
            $route = request()->route();
            $route->setParameter('application', $application);
            return $route;
        });

        $validated = $request->validate($formRequest->rules());
        
        $this->applicationService->updateProgram($application, $validated);

        if ($request->input('action') === 'next') {
            return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-4');
        }

        return back()->with('status', 'Step 3 Saved');
    }

    private function saveDocuments(Request $request, Application $application): RedirectResponse
    {
         if ($request->hasFile('national_id')) {
            $request->validate(['national_id' => 'file|mimes:jpeg,png,pdf|max:5120']);
            $this->documentService->store($application, $request->file('national_id'), 'national_id');
        }

        if ($request->hasFile('transcript')) {
            $request->validate(['transcript' => 'file|mimes:jpeg,png,pdf|max:5120']);
            $this->documentService->store($application, $request->file('transcript'), 'transcript');
        }

        if ($request->input('action') === 'finish') {
             try {
                // Validate documents and mark step 4 complete
                $this->applicationService->saveStep($application, 4, [], true);
                return redirect()->route('application.payment', $application);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => $e->getMessage()]);
            }
        }
        
        $this->applicationService->saveStep($application, 4, [], false);
        return back()->with('status', 'Documents Saved');
    }
}
