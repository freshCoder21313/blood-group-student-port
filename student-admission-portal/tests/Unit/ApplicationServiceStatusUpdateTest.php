<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\StatusHistory;
use App\Services\Application\ApplicationService;
use App\Events\ApplicationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('updateStatus updates application status and creates history', function () {
    Event::fake();

    $application = Application::factory()->create([
        'status' => 'pending_approval'
    ]);

    $service = new ApplicationService();

    // Act
    $service->updateStatus($application, 'approved');

    // Assert
    expect($application->fresh()->status)->toBe('approved');

    // Check history
    $this->assertDatabaseHas('status_histories', [
        'application_id' => $application->id,
        'from_status' => 'pending_approval',
        'to_status' => 'approved',
        'source' => 'system' // or whatever default
    ]);

    // Check event
    Event::assertDispatched(ApplicationStatusChanged::class, function ($event) use ($application) {
        return $event->application->id === $application->id 
            && $event->fromStatus === 'pending_approval'
            && $event->toStatus === 'approved';
    });
});

test('updateStatus throws exception for invalid transition', function () {
    $application = Application::factory()->create([
        'status' => 'draft'
    ]);

    $service = new ApplicationService();

    // Act & Assert
    // Assuming we can only update from pending_approval to approved/rejected
    // based on story AC "Verify valid state transition"
    expect(fn() => $service->updateStatus($application, 'approved'))
        ->toThrow(Exception::class);
});
