<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'quantity',
        'image',
        'price',
        'sale_price',
        'sale_start',
        'sale_end',
        'sku',
        'status',
    ];
    public function product(){
        return $this->belongsTo(Product::class);
    }

    
}
