<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        return response()->json(Patient::with('department')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mrn' => 'nullable|string|max:50|unique:patients,mrn',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact_number' => 'required|string',
            // 'email' => 'nullable|email',
            'gender' => 'required|string',
            // 'date_of_birth' => 'required|date',
            'address' => 'nullable|string',
            'department_id' => 'nullable|integer|exists:departments,id'
        ]);

        $patient = Patient::create($data);

        return response()->json([
            'message' => 'Patient added successfully',
            'patient' => $patient
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Patient::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $data = $request->validate([
            'mrn' => 'sometimes|nullable|string|max:50|unique:patients,mrn,' . $patient->id,
            'first_name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'contact_number' => 'sometimes|required|string',
            'gender' => 'sometimes|required|string',
            'address' => 'nullable|string',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $patient->update($data);

        return response()->json(['message' => 'Patient updated successfully']);
    }

    public function destroy($id)
    {
        Patient::findOrFail($id)->delete();

        return response()->json(['message' => 'Patient deleted successfully']);
    }
}
