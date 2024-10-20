<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'carts';
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'user_id',
        'guest_token',
        'quantity',
        'price'
    ];
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function productVariant(){
        return $this->belongsTo(ProductVariant::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
