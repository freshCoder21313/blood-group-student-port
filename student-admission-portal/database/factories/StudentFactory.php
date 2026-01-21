<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'nationality' => 'Kenya',
            'address' => fake()->address(),
            'city' => fake()->city(),
            'national_id' => fake()->numerify('########'),
            'passport_number' => fake()->bothify('?#######'),
        ];
    }
}
