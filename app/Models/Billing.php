<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'created_by',
        'approved_by',
        'total_amount',
        'status',
        'payment_method',
        'paid_amount',
        'balance_due',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(BillingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BillingPayment::class);
    }

    public function getAmountPaidAttribute(): float
    {
        return (float) ($this->paid_amount ?? 0);
    }

    public function getAmountDueAttribute(): float
    {
        return max(0, (float) ($this->balance_due ?? 0));
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
