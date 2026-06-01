<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffShift extends Model
{
    use HasFactory;

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
