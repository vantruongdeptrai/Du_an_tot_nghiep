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
        Schema::create('galleries', function (Blueprint $table) {
            $table->increments('id');// Khóa chính tự động tăng
            $table->unsignedInteger('product_id');// Khóa ngoại tham chiếu đến bảng products
            $table->string('image');// Đường dẫn đến hình ảnh
            $table->softDeletes();
            $table->timestamps();
            // Thiết lập khóa ngoại
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
