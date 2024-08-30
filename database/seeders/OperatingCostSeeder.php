<?php

namespace Database\Seeders;
use App\Models\OperatingCost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperatingCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OperatingCost::factory()->count(5)->create();
    }
}
