<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',  
        'address',
        'status',
        'password',
        'image',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    public function blogs(){
        return $this->hasMany(Blog::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function hasRole($role){
        return $this->name===$role;
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
    public function addresses()
{
    return $this->hasMany(Address::class);
}
public function comments()
{
    return $this->hasMany(Comment::class);
}

/**
 * Quan hệ với bảng ReplyComment (các câu trả lời của người dùng).
 */
public function replyComments()
{
    return $this->hasMany(ReplyComment::class);
}
public function getImageUrl()
    {
        return asset('storage/image/' . $this->image); // Giả sử ảnh được lưu trong thư mục storage/images
    }
}
