<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => \App\Models\Application::factory(),
            'transaction_code' => 'M' . $this->faker->regexify('[A-Z0-9]{9}'),
            'phone_number' => '2547' . $this->faker->numerify('########'),
            'amount' => 1000,
            'status' => 'pending',
            'merchant_request_id' => $this->faker->uuid,
            'checkout_request_id' => $this->faker->uuid,
            'mpesa_receipt_number' => null,
            'result_desc' => null,
        ];
    }
}
