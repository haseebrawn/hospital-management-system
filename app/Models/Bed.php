<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bed extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'ward_id',
        'bed_number',
        'status'
    ];

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function allocations()
    {
        return $this->hasMany(BedAllocation::class);
    }
}
