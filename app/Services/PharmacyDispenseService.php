<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineStockMovement;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyDispenseService
{
    public function dispense(Prescription $prescription, User $user): Prescription
    {
        return DB::transaction(function () use ($prescription, $user) {
            $prescription->loadMissing(['items.medicine', 'patient', 'doctor']);

            if ($prescription->status === 'dispensed') {
                throw ValidationException::withMessages([
                    'prescription' => 'This prescription is already dispensed.',
                ]);
            }

            $items = $prescription->items;

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'No prescription items were found to dispense.',
                ]);
            }

            $medicineLines = [];

            foreach ($items as $item) {
                $medicine = $this->resolveMedicine($item);
                $quantity = max(1, (int) ($item->quantity ?: 1));

                if (! $medicine) {
                    throw ValidationException::withMessages([
                        'items' => "Prescription item {$item->medicine_name} is not linked to an inventory medicine.",
                    ]);
                }

                $lockedMedicine = Medicine::whereKey($medicine->id)->lockForUpdate()->firstOrFail();

                if ($lockedMedicine->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "{$lockedMedicine->name} has insufficient stock. Requested {$quantity}, available {$lockedMedicine->stock}.",
                    ]);
                }

                $stockBefore = (int) $lockedMedicine->stock;
                $stockAfter = $stockBefore - $quantity;
                $lockedMedicine->update([
                    'stock' => $stockAfter,
                    'reorder_alert_sent' => false,
                ]);

                MedicineStockMovement::create([
                    'medicine_id' => $lockedMedicine->id,
                    'prescription_id' => $prescription->id,
                    'performed_by' => $user->id,
                    'movement_type' => 'dispense',
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference' => 'Prescription #' . $prescription->id,
                    'notes' => trim((string) ($item->instructions ?: $prescription->description)),
                ]);

                $medicineLines[] = $lockedMedicine->name . ' x' . $quantity;
            }

            $prescription->update([
                'status' => 'dispensed',
            ]);

            return $prescription->fresh(['items.medicine', 'patient', 'doctor']);
        });
    }

    public function recordOpeningStock(Medicine $medicine, User $user, int $stock): void
    {
        MedicineStockMovement::create([
            'medicine_id' => $medicine->id,
            'performed_by' => $user->id,
            'movement_type' => 'opening',
            'quantity' => $stock,
            'stock_before' => 0,
            'stock_after' => $stock,
            'reference' => 'Medicine #' . $medicine->id,
            'notes' => 'Opening stock recorded when medicine was created.',
        ]);

        $medicine->forceFill(['reorder_alert_sent' => false])->saveQuietly();
    }

    public function recordStockAdjustment(Medicine $medicine, User $user, int $before, int $after, string $notes): void
    {
        $delta = $after - $before;

        if ($delta === 0) {
            return;
        }

        MedicineStockMovement::create([
            'medicine_id' => $medicine->id,
            'performed_by' => $user->id,
            'movement_type' => 'adjustment',
            'quantity' => $delta,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference' => 'Medicine #' . $medicine->id,
            'notes' => $notes,
        ]);

        $medicine->forceFill(['reorder_alert_sent' => false])->saveQuietly();
    }

    private function resolveMedicine(PrescriptionItem $item): ?Medicine
    {
        if ($item->relationLoaded('medicine') && $item->medicine) {
            return $item->medicine;
        }

        if ($item->medicine_id) {
            return Medicine::find($item->medicine_id);
        }

        return Medicine::whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $item->medicine_name))])->first();
    }
}
