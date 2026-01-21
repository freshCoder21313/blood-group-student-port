<?php

use App\Events\ApplicationStatusChanged;
use App\Models\Application;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

test('update status request requires authentication', function () {
    $response = $this->postJson('/api/v1/sync/status', []);
    $response->assertStatus(401);
});

test('update status request validation fails for missing fields', function () {
    Sanctum::actingAs(
        User::factory()->create(),
        ['asp:sync']
    );

    $response = $this->postJson('/api/v1/sync/status', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['application_id', 'status']);
});

test('update status request validation fails for invalid status', function () {
    Sanctum::actingAs(
        User::factory()->create(),
        ['asp:sync']
    );

    $response = $this->postJson('/api/v1/sync/status', [
        'application_id' => 1,
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('successfully updates status and fires event', function () {
    Event::fake();

    Sanctum::actingAs(
        User::factory()->create(),
        ['asp:sync']
    );

    $application = Application::factory()->create([
        'status' => 'pending_approval'
    ]);

    $response = $this->postJson('/api/v1/sync/status', [
        'application_id' => $application->id,
        'status' => 'approved',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'approved',
    ]);

    $this->assertDatabaseHas('status_histories', [
        'application_id' => $application->id,
        'from_status' => 'pending_approval',
        'to_status' => 'approved',
        'source' => 'ASP',
    ]);

    Event::assertDispatched(ApplicationStatusChanged::class, function ($event) use ($application) {
        return $event->application->id === $application->id
            && $event->fromStatus === 'pending_approval'
            && $event->toStatus === 'approved';
    });
});

test('successfully updates status with comment', function () {
    Event::fake();

    Sanctum::actingAs(
        User::factory()->create(),
        ['asp:sync']
    );

    $application = Application::factory()->create([
        'status' => 'pending_approval'
    ]);

    $response = $this->postJson('/api/v1/sync/status', [
        'application_id' => $application->id,
        'status' => 'rejected',
        'comment' => 'Missing documents',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('status_histories', [
        'application_id' => $application->id,
        'to_status' => 'rejected',
        'notes' => 'Missing documents',
    ]);
});

test('successfully updates status and sends email', function () {
    Mail::fake();

    Sanctum::actingAs(
        User::factory()->create(),
        ['asp:sync']
    );

    $user = User::factory()->create(['email' => 'student@test.com']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval'
    ]);

    $response = $this->postJson('/api/v1/sync/status', [
        'application_id' => $application->id,
        'status' => 'approved',
    ]);

    $response->assertOk();

    Mail::assertSent(\App\Mail\ApplicationStatusUpdated::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('fails to update status if not pending_approval', function () {
    Sanctum::actingAs(
        User::factory()->create(),
        ['asp:sync']
    );

    $application = Application::factory()->create([
        'status' => 'rejected'
    ]);

    $response = $this->postJson('/api/v1/sync/status', [
        'application_id' => $application->id,
        'status' => 'approved',
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'rejected',
    ]);
});
