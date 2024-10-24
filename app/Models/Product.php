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
        'quantity',
        'slug',
        'price',
        'category_id',
        'sale_price',
        'sale_start',
        'sale_end',
        'new_product',
        'best_seller_product',
        'featured_product',
        'is_variant',
    ];

    protected $dates = [
        'sale_start',
        'sale_end',
    ];
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    protected $appends = ['category_name', 'highest_price', 'lowest_price', 'image_url', 'variants'];

    public function getCategoryNameAttribute()
    {
        return $this->category->name ?? null;
    }

    public function getHighestPriceAttribute()
    {
        return $this->productVariants->max('price') ?? $this->price;
    }

    public function getLowestPriceAttribute()
    {
        return $this->productVariants->min('price') ?? $this->price;
    }
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getVariantsAttribute()
    {
        return $this->productVariants->map(function ($variant) {
            return [
                'size' => $variant->size ? $variant->size->name : 'N/A',
                'color' => $variant->color ? $variant->color->name : 'N/A',
                'price' => $variant->price,
                'image_url' => $variant->image ? asset('storage/' . $variant->image) : null,
            ];
        });
    }

    
}
