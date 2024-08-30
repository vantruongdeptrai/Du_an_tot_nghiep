<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, softDeletes;
    protected $fillable=[
        'title',
        'content'
    ];
    protected $table='role';
}
