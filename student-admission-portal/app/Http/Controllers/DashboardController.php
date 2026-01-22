<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Application\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->load('student.application');
        $student = $user->student;

        // Check if user is an admitted student (has student_code)
        if ($student && $student->student_code) {
            // Optimization: Use eager loaded application from student relationship
            // $application = $this->applicationService->getCurrentApplication($user->id);
            
            return view('student.dashboard', [
                // 'application' => $application, // Unused in view
                'student' => $student
            ]);
        }

        $application = $this->applicationService->getCurrentApplication($user->id);
        
        return view('dashboard', [
            'application' => $application
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Application::class);
        
        $application = $this->applicationService->createDraft($request->user()->id);
        
        return redirect()->route('application.personal', $application);
    }
}
