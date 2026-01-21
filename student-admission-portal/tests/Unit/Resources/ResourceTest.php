<?php

use App\Http\Resources\V1\ApplicationResource;
use App\Http\Resources\V1\StudentResource;
use App\Http\Resources\V1\DocumentResource;
use App\Models\Application;
use App\Models\Student;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('ApplicationResource structure', function () {
    $application = Application::factory()->create();
    $resource = new ApplicationResource($application);
    $json = $resource->response()->getData(true);
    
    expect($json)->toHaveKey('data');
    expect($json['data'])->toHaveKey('id');
    expect($json['data'])->toHaveKey('status');
});

test('StudentResource includes decrypted pii', function () {
    $student = Student::factory()->create([
        'national_id' => '12345678',
        'passport_number' => 'A1234567',
    ]);
    
    $resource = new StudentResource($student);
    $json = $resource->response()->getData(true);
    
    expect($json['data']['national_id'])->toBe('12345678');
    expect($json['data']['passport_number'])->toBe('A1234567');
});

test('DocumentResource structure', function () {
    $document = Document::factory()->create();
    
    $resource = new DocumentResource($document);
    $json = $resource->response()->getData(true);
    
    expect($json['data'])->toHaveKey('id');
    expect($json['data'])->toHaveKey('path');
    expect($json['data'])->toHaveKey('download_url');
});
