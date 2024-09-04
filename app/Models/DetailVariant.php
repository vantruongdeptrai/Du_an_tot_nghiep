<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailVariant extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'product_variant_id',
        'attribute_value_id',
    ];
}
