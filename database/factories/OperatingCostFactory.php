<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OperatingCost>
 */
class OperatingCostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_type' => $this->faker->word,
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'description' => $this->faker->optional()->sentence,
        ];
    }
}
