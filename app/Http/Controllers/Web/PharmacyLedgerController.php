<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineStockMovement;
use Illuminate\Http\Request;

class PharmacyLedgerController extends Controller
{
    public function index(Request $request)
    {
        $medicineId = $request->query('medicine_id');
        $movementType = trim((string) $request->query('movement_type', ''));

        $movements = MedicineStockMovement::query()
            ->with(['medicine', 'prescription', 'performer'])
            ->when($medicineId, fn ($query) => $query->where('medicine_id', $medicineId))
            ->when($movementType !== '', fn ($query) => $query->where('movement_type', $movementType))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $medicines = Medicine::query()->orderBy('name')->get(['id', 'name', 'stock']);
        $movementTypes = ['opening', 'adjustment', 'dispense'];

        return view('modules.pharmacy.ledger', compact('movements', 'medicines', 'movementTypes', 'medicineId', 'movementType'));
    }
}
