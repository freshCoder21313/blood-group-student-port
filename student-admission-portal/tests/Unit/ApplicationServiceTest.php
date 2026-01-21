<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Document;
use App\Services\Application\ApplicationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

test('getDocuments returns application documents', function () {
    $application = Application::factory()->create();
    Document::create([
        'application_id' => $application->id, 
        'type' => 'transcript', 
        'path' => 'p1', 
        'original_name' => 'n1',
        'mime_type' => 'text/plain',
        'size' => 100
    ]);
    
    $service = new ApplicationService();
    $documents = $service->getDocuments($application);
    
    expect($documents)->toHaveCount(1)
        ->and($documents->first()->type)->toBe('transcript');
});

test('saveStep throws exception if required documents missing for step 4', function () {
    $service = new ApplicationService();
    $user = \App\Models\User::factory()->create();
    $application = $service->createDraft($user->id);
    
    // Step 4 is documents
    $service->saveStep($application, 4, [], true);
})->throws(Exception::class); // We expect specific message, but Exception class is enough for Red

test('saveStep succeeds for step 4 if documents present', function () {
    $service = new ApplicationService();
    $user = \App\Models\User::factory()->create();
    $application = $service->createDraft($user->id);
    
    Document::create(['application_id' => $application->id, 'type' => 'national_id', 'path' => 'p1', 'original_name' => 'n1', 'mime_type' => 't', 'size' => 1]);
    Document::create(['application_id' => $application->id, 'type' => 'transcript', 'path' => 'p2', 'original_name' => 'n2', 'mime_type' => 't', 'size' => 1]);
    
    $step = $service->saveStep($application, 4, [], true);
    
    expect($step->is_completed)->toBeTrue();
});
