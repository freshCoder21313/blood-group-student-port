<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Document;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can delete their own document', function () {
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
        ->deleteJson(route('documents.destroy', $document));
        
    $response->assertOk();
    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    Storage::disk('private')->assertMissing($path);
});

test('user cannot delete others document', function () {
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
        ->deleteJson(route('documents.destroy', $document))
        ->assertForbidden();
        
    $this->assertDatabaseHas('documents', ['id' => $document->id]);
});

test('uploading duplicate document type replaces old one', function () {
    Storage::fake('private');
    
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $this->actingAs($user);
    
    // Upload first file
    $file1 = UploadedFile::fake()->create('id_v1.jpg', 100);
    $this->post(route('application.documents.update', $application), [
        'national_id' => $file1,
        'action' => 'save'
    ]);
    
    $doc1 = Document::where('application_id', $application->id)->where('type', 'national_id')->first();
    $this->assertNotNull($doc1);
    $path1 = $doc1->path;
    
    // Upload second file (replacement)
    $file2 = UploadedFile::fake()->create('id_v2.jpg', 100);
    $this->post(route('application.documents.update', $application), [
        'national_id' => $file2,
        'action' => 'save'
    ]);
    
    // Check old one is gone
    $this->assertDatabaseMissing('documents', ['id' => $doc1->id]);
    Storage::disk('private')->assertMissing($path1);
    
    // Check new one exists
    $this->assertDatabaseCount('documents', 1);
    $doc2 = Document::where('application_id', $application->id)->where('type', 'national_id')->first();
    $this->assertEquals('id_v2.jpg', $doc2->original_name);
});
