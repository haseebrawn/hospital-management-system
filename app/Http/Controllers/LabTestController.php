<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\LabTestResource;

class LabTestController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
       $user = Auth::user();

        if ($user->hasRole('doctor')) {
            $tests = LabTest::where('doctor_id', $user->id)->get();
        } elseif ($user->hasRole('lab_technician')) {
            $tests = LabTest::where('lab_technician_id', $user->id)->get();
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return LabTestResource::collection($tests);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->hasRole('lab_technician')) {
            return response()->json(['message' => 'Only Lab Technicians can create tests'], 403);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'test_type' => 'required|string',
            'doctor_id' => 'nullable|exists:users,id'
        ]);

        $labTest = LabTest::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'lab_technician_id' => $user->id,
            'test_type' => $request->test_type,
        ]);

        return response()->json([
            'message' => 'Lab test created successfully',
            'data' => new LabTestResource($labTest)
        ], 201);
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $test = LabTest::find($id);

        if (! $test) {
            return response()->json(['message' => 'Lab test not found'], 404);
        }

        if (! $user->hasRole('lab_technician') || $test->lab_technician_id !== $user->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $request->validate([
            'status' => 'nullable|string',
            'results' => 'nullable|string'
        ]);

        $test->update($request->only('status', 'results'));

        return new LabTestResource($test);
    }

    public function destroy($id)
    {
        /** @var \App\Models\User $user */
       $user = Auth::user();
        $test = LabTest::find($id);

        if (! $test) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (! $user->hasRole('lab_technician')) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $test->delete();

        return response()->json(['message' => 'Lab test deleted successfully']);
    }
}
