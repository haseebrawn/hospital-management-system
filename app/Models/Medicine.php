<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'stock',
        'reorder_level',
        'price',
        'expiry_date',
        'expiry_alert_date',
        'expiry_alert_sent',
        'reorder_alert_sent',
        'status'
    ];

    public function stockMovements()
    {
        return $this->hasMany(MedicineStockMovement::class);
    }
}
