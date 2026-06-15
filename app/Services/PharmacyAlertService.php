<?php

namespace App\Services;

use App\Models\Medicine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PharmacyAlertService
{
    public function collectAlerts(): Collection
    {
        $today = now()->startOfDay();
        $expiryThreshold = now()->addDays(30)->endOfDay();

        return Medicine::query()
            ->where(function ($query) use ($today, $expiryThreshold) {
                $query->where(function ($expiryQuery) use ($today, $expiryThreshold) {
                    $expiryQuery->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<=', $expiryThreshold);
                })->orWhere(function ($reorderQuery) {
                    $reorderQuery->whereColumn('stock', '<=', 'reorder_level');
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function (Medicine $medicine) use ($today, $expiryThreshold) {
                $expiryDate = $medicine->expiry_date ? Carbon::parse($medicine->expiry_date) : null;
                $isExpiring = $expiryDate && $expiryDate->lessThanOrEqualTo($expiryThreshold);
                $isExpired = $expiryDate && $expiryDate->lessThan($today);
                $isLowStock = (int) $medicine->stock <= (int) ($medicine->reorder_level ?? 10);

                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'stock' => (int) $medicine->stock,
                    'reorder_level' => (int) ($medicine->reorder_level ?? 10),
                    'expiry_date' => $medicine->expiry_date,
                    'is_expired' => $isExpired,
                    'is_expiring' => $isExpiring,
                    'is_low_stock' => $isLowStock,
                ];
            });
    }
}
