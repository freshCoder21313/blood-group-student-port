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
        $application = $this->applicationService->getCurrentApplication($request->user()->id);
        
        return view('dashboard', [
            'application' => $application
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Application::class);
        $application = $this->applicationService->createDraft($request->user()->id);
        
        return redirect()->route('application.step', ['step' => 1]);
    }
}
