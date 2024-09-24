<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class Color extends Model
{
    use HasFactory, SoftDeletes; // Thêm SoftDeletes

    protected $fillable = ['name'];
}