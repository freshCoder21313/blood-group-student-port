<?php

declare(strict_types=1);

use App\Events\ApplicationStatusChanged;
use App\Listeners\SendStatusChangeEmail;
use App\Mail\ApplicationStatusUpdated;
use App\Models\Application;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('listener sends email when status changes', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'student@example.com']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'approved'
    ]);

    $event = new ApplicationStatusChanged($application, 'pending_approval', 'approved');
    $listener = new SendStatusChangeEmail();
    $listener->handle($event);

    Mail::assertSent(ApplicationStatusUpdated::class, function ($mail) use ($user, $application) {
        return $mail->hasTo($user->email) &&
               $mail->application->id === $application->id &&
               $mail->status === 'approved';
    });
});
