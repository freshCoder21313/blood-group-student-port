<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

test('store uploads file and creates document record', function () {
    Storage::fake('private');
    
    $application = Application::factory()->create();
    $file = UploadedFile::fake()->create('test.jpg', 100);
    
    $service = new DocumentService();
    $document = $service->store($application, $file, 'national_id');
    
    expect($document)->toBeInstanceOf(Document::class)
        ->and($document->type)->toBe('national_id')
        ->and($document->original_name)->toBe('test.jpg')
        ->and($document->application_id)->toBe($application->id);
        
    Storage::disk('private')->assertExists($document->path);
    $this->assertDatabaseHas('documents', ['id' => $document->id]);
});

test('delete removes file and record', function () {
    Storage::fake('private');
    $service = new DocumentService();
    
    $application = Application::factory()->create();
    $path = 'documents/test.jpg';
    Storage::disk('private')->put($path, 'content');
    
    $document = Document::create([
        'application_id' => $application->id,
        'type' => 'test',
        'path' => $path,
        'original_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);
    
    $service->delete($document);
    
    Storage::disk('private')->assertMissing($path);
    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
});
