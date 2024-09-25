<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductFactory extends Factory
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
            'name' => $name,  // Tên sản phẩm giả lập
            'image' => $this->faker->url,  // URL ảnh giả lập
            'description' => $this->faker->paragraph, // Mô tả giả lập
            'slug' => Str::slug($name), // Slug giả lập
            'price' => $this->faker->randomFloat(2, 100, 10000), // Giá giả lập
            'category_id' => Category::all()->random()->id, // Tạo liên kết với Category giả lập
            'sale_price' => $this->faker->optional()->randomFloat(2, 50, 5000), // Giá sale giả lập, có thể null
            'sale_start' => $this->faker->optional()->dateTimeBetween('-1 month', '+1 month'), // Thời gian bắt đầu sale
            'sale_end' => $this->faker->optional()->dateTimeBetween('+1 day', '+2 months'), // Thời gian kết thúc sale
            'new_product' => $this->faker->boolean, // Sản phẩm mới
            'best_seller_product' => $this->faker->boolean, // Sản phẩm bán chạy
            'featured_product' => $this->faker->boolean, // Sản phẩm nổi bật
            'is_variant'=>$this->faker->boolean(80),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
