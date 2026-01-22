<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdmissionLetterController extends Controller
{
    public function download(Application $application)
    {
        // Allow student or admin to view
        // Note: auth()->user() might be null if not protected by middleware, but route has 'auth'
        if (!Gate::allows('view', $application) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Only approved applications
        if ($application->status !== 'approved' && $application->status !== 'student') {
            abort(404, 'Admission letter not available.');
        }

        $student = $application->student;
        $program = $application->program;
        $block = $application->academicBlock;

        $pdf = Pdf::loadView('pdf.admission-letter', [
            'application' => $application,
            'student' => $student,
            'program' => $program,
            'block' => $block,
        ]);

        return $pdf->download('Admission_Letter_' . $student->first_name . '.pdf');
    }
}
