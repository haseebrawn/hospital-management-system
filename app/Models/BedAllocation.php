<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedAllocation extends Model
{
    protected $fillable = 
    [
        'patient_id', 
        'bed_id', 
        'assigned_at', 
        'released_at'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}

