<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class OrderCoupon extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'order_coupons'; 

    protected $fillable = [
        'order_id',
        'coupon_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
