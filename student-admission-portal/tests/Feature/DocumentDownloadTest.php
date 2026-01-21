<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Document;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can download their own document', function () {
    Storage::fake('private');
    
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $file = UploadedFile::fake()->create('test.txt', 100);
    $path = $file->store('documents', 'private');
    
    $document = Document::create([
        'application_id' => $application->id,
        'type' => 'transcript',
        'path' => $path,
        'original_name' => 'test.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);
    
    $response = $this->actingAs($user)
        ->get("/documents/{$document->id}");
        
    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    $response->assertDownload('test.txt');
});

test('user cannot download others document', function () {
    Storage::fake('private');
    
    $otherUser = User::factory()->create();
    $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
    $otherApp = Application::factory()->create(['student_id' => $otherStudent->id]);
    
    $file = UploadedFile::fake()->create('secret.txt', 100);
    $path = $file->store('documents', 'private');
    
    $document = Document::create([
        'application_id' => $otherApp->id,
        'type' => 'transcript',
        'path' => $path,
        'original_name' => 'secret.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);
    
    $myself = User::factory()->create();
    
    $this->actingAs($myself)
        ->get("/documents/{$document->id}")
        ->assertForbidden();
});
