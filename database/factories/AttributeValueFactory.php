<?php

namespace Database\Factories;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id'=>Attribute::all()->random()->id,
            'value' => $this->faker->word,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
