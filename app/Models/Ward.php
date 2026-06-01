<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name',
        'department_id', 
        'capacity'
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
