<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AttributeValue;
use App\Models\OperatingCost;
use App\Models\Stock;
use App\Models\Tag;
use Attribute;
use Faker\Core\Color;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            OperatingCostSeeder::class,
            TagSeeder::class,
            OperatingCostSeeder::class,
            TagSeeder::class,
            ProductSeeder::class,   
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ColorSeeder::class,
            SizeSeeder::class,
            StockSeeder::class,
            ProductVariantSeeder::class,
            UserSeeder::class,
           CartSeeder::class,
           
        ]);
        
        

    }
}
