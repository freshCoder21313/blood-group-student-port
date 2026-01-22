<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicBlock>
 */
class AcademicBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Fall ' . $this->faker->year,
            'code' => 'BLOCK-' . $this->faker->unique()->numberBetween(100, 999),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'is_active' => true,
        ];
    }
}
