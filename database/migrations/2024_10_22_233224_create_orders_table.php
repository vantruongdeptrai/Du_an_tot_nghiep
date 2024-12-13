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
            $table->unsignedInteger('user_id')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->enum('status_order', [
'Chờ xác nhận','Đã xác nhận','Đang chuẩn bị','Đang vận chuyển','Giao hàng thành công','Đã hủy','Đã thanh toán','Chờ xác nhận hủy','Đã nhận hàng','Chưa nhận hàng'
            ]);
            $table->string('payment_type');
            $table->text('shipping_address');
            $table->text('user_note')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('phone_order');
            $table->string('name_order');
            $table->string('email_order');
            $table->text('cancel_reason')->nullable(); 
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
