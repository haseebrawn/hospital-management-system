<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;
use App\Services\PharmacyDispenseService;
use Illuminate\Validation\ValidationException;

class PharmacyController extends Controller
{
    public function pendingPrescriptions()
    {
        return response()->json(
            Prescription::where('status', 'pending')->with(['patient', 'doctor', 'items.medicine'])->get()
        );
    }

    public function dispense($id, PharmacyDispenseService $pharmacyDispenseService, Request $request)
    {
        $prescription = Prescription::find($id);

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        try {
            $prescription = $pharmacyDispenseService->dispense($prescription, $request->user());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Unable to dispense prescription.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Prescription dispensed successfully.',
            'data' => $prescription,
        ]);
    }
}
