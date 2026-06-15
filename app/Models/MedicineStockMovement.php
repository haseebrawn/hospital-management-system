<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'prescription_id',
        'performed_by',
        'movement_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference',
        'notes',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
