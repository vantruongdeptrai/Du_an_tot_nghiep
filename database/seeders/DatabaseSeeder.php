<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AttributeValue;
use App\Models\OperatingCost;
use App\Models\Stock;
use App\Models\Tag;
use Attribute;
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
            roleSeeder::class,
            PermissionsSeeder::class,
            CategorySeeder::class,
            OperatingCostSeeder::class,
            TagSeeder::class,
            roleSeeder::class,
            OperatingCostSeeder::class,
            TagSeeder::class,
            StockSeeder::class,
            CategorySeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            DetailVariantSeeder::class,
        ]);
        
        

    }
}
