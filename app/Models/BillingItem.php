<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'service_name',
        'price',
        'quantity',
        'type',
        'source_type',
        'source_id',
        'source_name',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }
}
