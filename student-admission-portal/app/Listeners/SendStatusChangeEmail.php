<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Mail\ApplicationStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendStatusChangeEmail implements ShouldQueue
{
    public function handle(ApplicationStatusChanged $event): void
    {
        $student = $event->application->student;
        
        if ($student && $student->user && $student->user->email) {
            Mail::to($student->user->email)->send(
                new ApplicationStatusUpdated($event->application, $event->toStatus)
            );
        } else {
            Log::warning('Cannot send status change email: User email not found for application.', [
                'application_id' => $event->application->id,
                'student_id' => $student->id ?? null
            ]);
        }
    }
}
