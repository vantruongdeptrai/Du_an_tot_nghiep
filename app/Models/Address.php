<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'street', 'ward', 'district', 'city', 'zip_code', 'country', 'is_default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return $this->street . ', ' . $this->ward . ', ' . $this->district . ', ' . $this->city . ', ' . $this->country . ($this->zip_code ? ' - ' . $this->zip_code : '');
    }
}

