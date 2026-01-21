<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'program_id' => null,
            'block_id' => null,
            'application_number' => $this->faker->unique()->bothify('APP-####-????'),
            'status' => 'draft',
            'current_step' => 1,
            'total_steps' => 4,
        ];
    }
}
