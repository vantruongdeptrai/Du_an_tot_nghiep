<?php

namespace Database\Factories;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReplyComment>
 */
class ReplyCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'comment_id' => Comment::all()->random()->id, // ID comment ngẫu nhiên
    'user_id' => User::all()->random()->id,       // ID người trả lời ngẫu nhiên
    'reply' => $this->faker->sentence(),                // Nội dung trả lời ngẫu nhiên
        ];
    }
}
