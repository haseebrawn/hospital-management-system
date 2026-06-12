<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mrn',
        'first_name', 
        'last_name', 
        'contact_number', 
        'gender',
        // 'date_of_birth', 
        'address', 
        'department_id'
    ];

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (! empty($patient->mrn)) {
                $patient->mrn = static::normalizeMrn($patient->mrn);
                return;
            }

            $patient->mrn = static::generateMrn(((int) static::max('id')) + 1);
        });
    }

    public static function generateMrn(int $number): string
    {
        return 'HMS-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public static function normalizeMrn(string $mrn): string
    {
        return strtoupper(trim($mrn));
    }

    public function setMrnAttribute(?string $value): void
    {
        $this->attributes['mrn'] = $value ? static::normalizeMrn($value) : null;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
