<?php

namespace Database\Factories;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::all()->random()->id, // ID sản phẩm ngẫu nhiên
            'user_id' => User::all()->random()->id,       // ID người dùng ngẫu nhiên
            'comment' => $this->faker->sentence(),              // Nội dung bình luận ngẫu nhiên
            'rating' => $this->faker->numberBetween(1, 5),      // Đánh giá từ 1 đến 5
        ];
    }
}
