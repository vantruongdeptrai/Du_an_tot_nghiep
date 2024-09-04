<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;



class roleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for($i=0;$i<5;$i++){
            DB::table('roles')->insert([
                'name' => 'Role ' . ($i + 1),
            ]);
        }  
    }
}
