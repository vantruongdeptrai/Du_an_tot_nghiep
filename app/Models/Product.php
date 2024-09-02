<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'image',
        'description',
        'slug',
        'price',
        'category_id',
        'sale_price',
        'sale_start',
        'sale_end',
        'new_product',
        'best_seller_product',
        'featured_product',
    ];

    protected $dates = [
        'sale_start', 
        'sale_end',
    ];
}
