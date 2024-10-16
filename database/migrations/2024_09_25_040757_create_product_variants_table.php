<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('color_id');
            $table->unsignedInteger('size_id');
            $table->integer('quantity'); // số lượng
            $table->string('image')->nullable(); // trường ảnh (dạng chuỗi)
            $table->decimal('price', 8, 2); // giá
            $table->decimal('sale_price', 15, 2)->nullable(); // Giá sale của sản phẩm
            $table->timestamp('sale_start')->nullable(); // Thời gian bắt đầu sale
            $table->timestamp('sale_end')->nullable(); // Thời gian kết thúc sale
            $table->string('sku')->unique(); // mã sku duy nhất
            $table->boolean('status')->default(true); // trạng thái
            $table->softDeletes();
            $table->timestamps();

            // Thiết lập khóa ngoại
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
            $table->foreign('size_id')->references('id')->on('sizes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
