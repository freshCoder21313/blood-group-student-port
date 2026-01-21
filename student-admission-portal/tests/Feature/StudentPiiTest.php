<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('encrypts pii in database', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'national_id' => '12345678',
        'passport_number' => 'A1234567',
    ]);

    $dbStudent = DB::table('students')->where('id', $student->id)->first();

    expect($dbStudent->national_id)->not->toBe('12345678')
        ->and(strlen($dbStudent->national_id))->toBeGreaterThan(20);
        
    expect($dbStudent->passport_number)->not->toBe('A1234567')
        ->and(strlen($dbStudent->passport_number))->toBeGreaterThan(20);
});

it('decrypts pii on retrieval', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'national_id' => '12345678',
        'passport_number' => 'A1234567',
    ]);

    $retrievedStudent = Student::find($student->id);

    expect($retrievedStudent->national_id)->toBe('12345678')
        ->and($retrievedStudent->passport_number)->toBe('A1234567');
});

it('populates blind indexes', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'national_id' => '12345678',
        'passport_number' => 'A1234567',
    ]);

    $dbStudent = DB::table('students')->where('id', $student->id)->first();

    expect($dbStudent->national_id_index)->not->toBeNull()
        ->and($dbStudent->national_id_index)->not->toBe('12345678');
        
    expect($dbStudent->passport_number_index)->not->toBeNull()
        ->and($dbStudent->passport_number_index)->not->toBe('A1234567');
});

it('clears blind index when pii is cleared', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'national_id' => '12345678',
    ]);
    
    expect($student->national_id_index)->not->toBeNull();
    
    $student->update(['national_id' => null]);
    $student->refresh();
    
    expect($student->national_id)->toBeNull();
    expect($student->national_id_index)->toBeNull();
});

it('can find student by blind index', function () {
    $user = User::factory()->create();
    $nationalId = '12345678';
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'national_id' => $nationalId,
    ]);
    
    // Test using the scope (Abstraction)
    $foundStudent = Student::whereNationalId($nationalId)->first();
    
    expect($foundStudent)->not->toBeNull()
        ->and($foundStudent->id)->toBe($student->id);
        
    // Verify raw query still works (Implementation detail)
    $hashedIndex = hash_hmac('sha256', $nationalId, config('app.blind_index_key'));
    $rawFound = Student::where('national_id_index', $hashedIndex)->first();
    expect($rawFound->id)->toBe($student->id);
});
