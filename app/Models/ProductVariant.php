<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'sku',
        'status',
    ];
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function detailVariants(){
        return $this->hasMany(DetailVariant::class);
    }
    
}
