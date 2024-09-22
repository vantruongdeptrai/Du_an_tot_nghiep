<?php

namespace Database\Seeders;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('attributes')->insert([
            ['name' => 'Size'],
            ['name' => 'Color']
        ]);
        
        $attributes = Attribute::query()->get(); // Get all
        $list_sizes = ['S', 'L', 'XL'];
        $list_colors = ['red', 'black', 'pink'];
        // Insert attribute values (Size, Color)
        $attributes = Attribute::query()->get();
        foreach ($attributes as $attribute) {
            if ($attribute->name == 'Size') {
                foreach ($list_sizes as $size) {
                    DB::table('attribute_values')->insert([
                        'attribute_id' => $attribute->id,
                        'value' => $size
                    ]);
                }
            } elseif ($attribute->name == 'Color') {
                foreach ($list_colors as $color) {
                    DB::table('attribute_values')->insert([
                        'attribute_id' => $attribute->id,
                        'value' => $color
                    ]);
                }
            }
        }

        // Insert products
        $category_id = Category::query()->get()->pluck('id');
        foreach ($category_id as $cat_id) {
            $name = Str::random(5);
            DB::table('products')->insert([
                'name' => $name,
                'description' => Str::random(10),
                'slug' => Str::slug($name),
                'price' => 1000000,
                'sale_price' => 899000,
                'category_id' => $cat_id,
                'sale_start' => now(),
                'sale_end' => now()->addDays(7),
                'new_product' => 0,
                'best_seller_product' => 0,
                'featured_product' => 0
            ]);
        }

        // Insert product variants
        $product_id = Product::query()->get()->pluck('id');
        foreach ($product_id as $prod_id) {
            DB::table('product_variants')->insert([
                'product_id' => $prod_id,
                'quantity' => 100,
                'price' => rand(500000, 700000),
                'sku' => strtoupper(Str::random(7)),
                'status' => 0
            ]);
        }

        // Insert detail variants (mapping between product_variants and attribute_values)
        $product_variant_id = DB::table('product_variants')->pluck('id');
        $attribute_value_id = DB::table('attribute_values')->pluck('id');
        foreach ($product_variant_id as $prod_var_id) {
            foreach ($attribute_value_id as $attr_val_id) {
                DB::table('detail_variants')->insert([
                    'product_variant_id' => $prod_var_id,
                    'attribute_value_id' => $attr_val_id
                ]);
            }
        }
    }
}
