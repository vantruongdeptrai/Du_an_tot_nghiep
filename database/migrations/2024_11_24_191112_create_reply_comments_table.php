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
        Schema::create('reply_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('comment_id'); // Liên kết đến comment gốc
            $table->unsignedInteger('user_id'); // Người dùng trả lời
            $table->text('reply'); // Nội dung trả lời
            $table->softDeletes();
            $table->timestamps();
            // Thiết lập khóa ngoại
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_comments');
    }
};
