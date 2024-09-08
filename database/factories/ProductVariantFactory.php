<?php

namespace Database\Factories;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::all();
        return [
            'product_id' => $product->isNotEmpty() ? $product->random()->id : Product::factory(),//Kiểm tra xem collection có trống hay không, nếu không trống lấy id ngẫu nhiên, nếu trống tạo product mới
            'quantity'=>$this->faker->numberBetween(1,100),// Số lượng ngẫu nhiên từ 1 đến 100
            'price'=>$this->faker->randomFloat(2, 10, 1000),// Giá ngẫu nhiên từ 10 đến 1000 với 2 chữ số thập phân
            'sku'=>$this->faker->unique()->bothify('SKU-#####'),// mã sku ngẫu nhiên duy nhất, sử dụng bothify để tạo chuỗi ký tự và số ngẫu nhiên.
            'status'=>$this->faker->boolean(80),//trạng thá ngẫu nhiên 80% là true

        ];
    }
}
