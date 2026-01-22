<?php

use App\Services\Student\MockStudentInformationService;

test('getGrades returns expected structure and values', function () {
    $service = new MockStudentInformationService();
    $grades = $service->getGrades('S12345');

    expect($grades)->toBeArray()
        ->not->toBeEmpty()
        ->and($grades[0])->toHaveKeys(['code', 'name', 'grade'])
        ->and($grades[0]['code'])->toBeString()
        ->and($grades[0]['grade'])->toBeString();
});

test('getSchedule returns expected structure and values', function () {
    $service = new MockStudentInformationService();
    $schedule = $service->getSchedule('S12345');

    expect($schedule)->toBeArray()
        ->not->toBeEmpty()
        ->and($schedule[0])->toHaveKeys(['day', 'time', 'course', 'venue'])
        ->and($schedule[0]['day'])->toBeString();
});

test('getFees returns expected structure including invoice history', function () {
    $service = new MockStudentInformationService();
    $fees = $service->getFees('S12345');

    expect($fees)->toBeArray()
        ->toHaveKeys(['balance', 'currency', 'status', 'invoice_history'])
        ->and($fees['balance'])->toBeInt()
        ->and($fees['invoice_history'])->toBeArray()
        ->and($fees['invoice_history'][0])->toHaveKeys(['invoice_id', 'amount', 'status']);
});
