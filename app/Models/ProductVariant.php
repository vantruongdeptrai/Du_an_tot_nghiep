<?php

namespace App\Models;
use Carbon\Carbon;
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
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    protected $appends = ['image_url', 'final_price'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    public function getFinalPriceAttribute()
    {
        $currentDate = Carbon::now();

        if ($this->sale_start && $this->sale_end &&
            $currentDate->between($this->sale_start, $this->sale_end)) {
            return $this->sale_price ?? $this->price;
        }

        return $this->price;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }



}
