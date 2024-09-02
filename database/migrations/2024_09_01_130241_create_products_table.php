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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2); // Giá gốc của sản phẩm
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->decimal('sale_price', 15, 2)->nullable(); // Giá sale của sản phẩm
            $table->timestamp('sale_start')->nullable(); // Thời gian bắt đầu sale
            $table->timestamp('sale_end')->nullable(); // Thời gian kết thúc sale
            $table->boolean('new_product')->default(false);
            $table->boolean('best_seller_product')->default(false);
            $table->boolean('featured_product')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
