<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function pendingPrescriptions()
    {
        return response()->json(
            Prescription::where('status', 'pending')->with('patient.user')->get()
        );
    }

    public function dispense($id)
    {
        $prescription = Prescription::find($id);

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        $prescription->update(['status' => 'dispensed']);

        return response()->json(['message' => 'Prescription marked as dispensed']);
    }
}
