<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PrescriptionRequest;
use App\Http\Resources\PrescriptionResource;

class PrescriptionController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
       $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            $prescriptions = Prescription::with(['appointment','doctor','patient'])->get();
        } else {
            // Doctors see only their own prescriptions
            $prescriptions = Prescription::where('doctor_id', $user->id)->get();
        }

        return PrescriptionResource::collection($prescriptions);
    }

    public function store(PrescriptionRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->hasRole(['doctor', 'super_admin'])) {
            return response()->json(['message' => 'Not Authorized'], 403);
        }

        $appointment = Appointment::findOrFail($request->appointment_id);

        $prescription = Prescription::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $user->id,
            'patient_id' => $appointment->patient_id,
            'description' => $request->description,
            'medicines' => $request->medicines,
        ]);

        return response()->json([
            'message' => 'Prescription created successfully!',
            'data' => new PrescriptionResource($prescription),
        ], 201);
    }
}
