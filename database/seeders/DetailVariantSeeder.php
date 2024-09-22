<?php

namespace Database\Seeders;
use App\Models\DetailVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetailVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DetailVariant::factory(5)->create();  
    }
}
