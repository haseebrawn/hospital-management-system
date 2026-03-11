<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
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
