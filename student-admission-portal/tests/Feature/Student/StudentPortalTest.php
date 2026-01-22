<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('grades page loads and shows data', function () {
    $response = $this->actingAs($this->user)->get(route('student.grades'));

    $response->assertOk();
    $response->assertSee('CS101');
    $response->assertSee('Intro to CS');
});

test('schedule page loads and shows data', function () {
    $response = $this->actingAs($this->user)->get(route('student.schedule'));

    $response->assertOk();
    $response->assertSee('Monday');
    $response->assertSee('Room A');
});

test('fees page loads and shows data', function () {
    $response = $this->actingAs($this->user)->get(route('student.fees'));

    $response->assertOk();
    $response->assertSee('50,000');
    $response->assertSee('INV-001');
});



test('guest cannot access grades page', function () {
    $this->get(route('student.grades'))->assertRedirect(route('login'));
});

test('grades page loads data for specific linked student', function () {
    $student = \App\Models\Student::factory()->create([
        'user_id' => $this->user->id,
        'student_code' => 'STU999'
    ]);

    $this->mock(\App\Services\Student\StudentInformationServiceInterface::class, function ($mock) {
        $mock->shouldReceive('getGrades')
             ->with('STU999')
             ->once()
             ->andReturn([
                 ['code' => 'CS999', 'name' => 'Advanced Testing', 'grade' => 'A']
             ]);
    });

    $response = $this->actingAs($this->user)->get(route('student.grades'));

    $response->assertOk();
    $response->assertSee('CS999');
});

test('grades page gracefully handles service failure', function () {
    $this->mock(\App\Services\Student\StudentInformationServiceInterface::class, function ($mock) {
        $mock->shouldReceive('getGrades')->andThrow(new \Exception('Service Down'));
    });

    $response = $this->actingAs($this->user)->get(route('student.grades'));

    $response->assertOk();
    $response->assertSessionHas('error', 'Unable to retrieve grades.');
});


