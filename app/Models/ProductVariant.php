<?php

namespace App\Models;

use App\Models\DetailVariant;
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
        'sku',
        'status',
    ];
    public function product(){
        return $this->belongsTo(Product::class);
    }

    
}
