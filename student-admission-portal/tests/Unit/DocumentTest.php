<?php

declare(strict_types=1);

use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('documents table has expected columns', function () {
    $columns = [
        'id',
        'application_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'created_at',
        'updated_at',
    ];

    expect(Schema::hasColumns('documents', $columns))->toBeTrue();
});

test('document model exists and has fillable attributes', function () {
    $document = new Document();
    
    $expectedFillable = [
        'application_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    expect($document->getFillable())->toEqual($expectedFillable);
});
