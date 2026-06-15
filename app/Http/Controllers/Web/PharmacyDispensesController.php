<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Services\PharmacyDispenseService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PharmacyDispensesController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $prescriptions = Prescription::query()
            ->with(['patient', 'doctor', 'items.medicine'])
            ->where('status', 'pending')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('description', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($patientQuery) use ($search) {
                            $patientQuery->where('mrn', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $lowStockMedicines = Medicine::query()
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->get();

        return view('modules.pharmacy.dispense', compact('prescriptions', 'search', 'lowStockMedicines'));
    }

    public function store(Request $request, Prescription $prescription, PharmacyDispenseService $pharmacyDispenseService)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin', 'pharmacist']), 403);

        try {
            $pharmacyDispenseService->dispense($prescription, $request->user());
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput();
        }

        return redirect()
            ->route('pharmacy.dispense.index')
            ->with('status', 'Prescription dispensed successfully and stock was updated.');
    }
}
