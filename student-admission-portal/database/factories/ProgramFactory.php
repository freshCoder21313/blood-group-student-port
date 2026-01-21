<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('PROG-###'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'duration' => '4 Years',
            'fee' => 50000,
            'is_active' => true,
        ];
    }
}
