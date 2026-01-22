<?php

use App\Services\Student\MockStudentInformationService;
use App\Services\Student\StudentInformationServiceInterface;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

uses(TestCase::class);

test('it binds mock service when driver is mock', function () {
    Config::set('services.student_info.driver', 'mock');
    
    $service = app(StudentInformationServiceInterface::class);
    
    expect($service)->toBeInstanceOf(MockStudentInformationService::class);
});

test('it throws exception for unknown driver', function () {
    Config::set('services.student_info.driver', 'unknown_driver');
    
    expect(fn() => app(StudentInformationServiceInterface::class))
        ->toThrow(RuntimeException::class, 'Unknown Student Information Driver: unknown_driver');
});
