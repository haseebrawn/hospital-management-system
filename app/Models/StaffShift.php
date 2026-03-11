<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffShift extends Model
{
    protected $fillable = [
        'staff_id',
        'shift_name',
        'shift_start',
        'shift_end',
        'shift_date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
