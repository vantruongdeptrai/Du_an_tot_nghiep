<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReplyComment extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'comment_id', // ID của comment gốc
        'user_id',    // ID người trả lời
        'reply',      // Nội dung trả lời
    ];

    /**
     * Quan hệ với bảng Comment.
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Quan hệ với bảng User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
