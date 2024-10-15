<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('title');       
            $table->string('image');         
            $table->text('content_blog');    
            $table->softDeletes();           
            $table->timestamps();     
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
