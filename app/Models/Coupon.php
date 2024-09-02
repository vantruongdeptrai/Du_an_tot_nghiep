<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'discount_amount',
        'min_order_value',
        'usage_limit',
        'is_active',
        'start_date',
        'end_date',
    ];
}
