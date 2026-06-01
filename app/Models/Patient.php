<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

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
