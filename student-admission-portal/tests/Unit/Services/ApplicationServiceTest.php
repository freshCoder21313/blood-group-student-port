<?php

use App\Models\User;
use App\Services\Application\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('createDraft sets status to draft and defaults', function () {
    $user = User::factory()->create();
    $service = new ApplicationService();
    
    $application = $service->createDraft($user->id);
    
    expect($application->status)->toBe('draft')
        ->and($application->program_id)->toBeNull()
        ->and($application->block_id)->toBeNull()
        ->and($application->current_step)->toBe(1)
        ->and($application->total_steps)->toBe(5);
});

test('createDraft generates unique application number', function () {
    $user = User::factory()->create();
    $service = new ApplicationService();
    
    $app1 = $service->createDraft($user->id);
    $app2 = $service->createDraft(User::factory()->create()->id);
    
    expect($app1->application_number)->not->toBe($app2->application_number)
        ->and($app1->application_number)->toContain('APP-' . date('Y'));
});
