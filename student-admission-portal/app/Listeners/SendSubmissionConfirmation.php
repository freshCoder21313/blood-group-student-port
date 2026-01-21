<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Mail\SubmissionConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendSubmissionConfirmation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        $application = $event->application;
        // Ensure relationships are loaded if they aren't already
        if (!$application->relationLoaded('student')) {
            $application->load('student.user');
        } elseif (!$application->student->relationLoaded('user')) {
             $application->student->load('user');
        }

        if ($application->student && $application->student->user && $application->student->user->email) {
             Mail::to($application->student->user->email)->send(new SubmissionConfirmation($application));
        }
    }
}
