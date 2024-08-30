<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class OperatingCost extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "operating_costs";
    protected $primaryKey = "id";
    protected $fillable = [
        'cost_type',
        'amount',
        'description'
    ];
}
