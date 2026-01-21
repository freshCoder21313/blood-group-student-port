<?php

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'type' => $this->faker->randomElement(['passport', 'result_slip']),
            'path' => 'documents/' . $this->faker->uuid . '.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1024, 10240),
        ];
    }
}
