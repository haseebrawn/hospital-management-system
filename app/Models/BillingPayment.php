<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'received_by',
        'amount',
        'payment_method',
        'reference',
        'notes',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
