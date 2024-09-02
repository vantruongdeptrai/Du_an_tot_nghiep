<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name=$this->faker->word;
        return [
            'name'=>$name,
            'image'=>$this->faker->url, 
            'slug' => Str::slug($name),
            'created_at'=>$this->faker->dateTime,
            'updated_at'=>$this->faker->dateTime
        ];
    }
}
