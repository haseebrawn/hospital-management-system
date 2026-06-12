<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'department_id',
        'date',
        'time',
        'reason',
        'notes',
        'status',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function getVisitStatusAttribute(): string
    {
        if ($this->checked_out_at) {
            return 'checked_out';
        }

        if ($this->checked_in_at) {
            return 'checked_in';
        }

        return 'not_checked_in';
    }

    public function canCheckIn(): bool
    {
        return ! $this->checked_in_at && $this->status === 'approved';
    }

    public function canCheckOut(): bool
    {
        return (bool) $this->checked_in_at && ! $this->checked_out_at && $this->status !== 'cancelled';
    }

    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function doctor() {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
