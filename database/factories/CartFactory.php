<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'user_id' => User::all()->random()->id,
            'product_id' => Product::all()->random()->id,
            'product_variant_id' => ProductVariant::all()->random()->id,
            'quantity' => $this->faker->numberBetween(1, 1000),
        ];
    }
}
