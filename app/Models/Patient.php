<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'first_name', 
        'last_name', 
        'contact_number', 
        'gender',
        // 'date_of_birth', 
        'address', 
        'department_id'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

