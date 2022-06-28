<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'reference' => strtoupper($this->faker->word()),
            'currency' => 'EUR',
            'status' => 'ordered',
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'ordered_at' => $this->faker->dateTimeBetween('-2 years'),
        ];
    }
}
