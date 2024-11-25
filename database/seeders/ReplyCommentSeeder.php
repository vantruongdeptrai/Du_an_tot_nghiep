<?php

namespace Database\Seeders;

use App\Models\ReplyComment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReplyCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReplyComment::factory(5)->create(); 
    }
}
