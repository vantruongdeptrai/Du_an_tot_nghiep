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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->decimal('total_price', 10, 2);
            $table->enum('status_order', ['chờ xử lý', 'đang xử lý', 'đã gửi hàng', 'hoàn thành', 'đã hủy']);
            $table->string('payment_type');
            $table->text('shipping_address');
            $table->text('user_note')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('phone_order');
            $table->string('name_order');
            $table->string('email_order');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');

            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
