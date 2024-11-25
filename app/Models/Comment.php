<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['product_id', 'user_id', 'comment', 'rating'];
     /**
     * Quan hệ với bảng Product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quan hệ với bảng User (người viết comment).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với bảng ReplyComment (các câu trả lời cho comment này).
     */
    public function replyComments()
    {
        return $this->hasMany(ReplyComment::class);
    }
}
