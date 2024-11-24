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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('street');
            $table->string('ward');
            $table->string('district');
            $table->string('city');
            $table->string('zip_code')->nullable();
            $table->string('country')->default('Vietnam');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();  // Optional for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
