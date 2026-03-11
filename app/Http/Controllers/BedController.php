<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BedController extends Controller
{
    // Show beds (filtered for Nurses)
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Bed::with('ward');

        if ($user->hasRole('nurse')) {
            $query->whereHas('ward', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        return response()->json($query->get()); 
    }

    // Create Bed
    public function store(Request $request)
    {
        $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'bed_number' => 'required|string',
        ]);

        $ward = Ward::findOrFail($request->ward_id);

        // Check ward capacity limit
        if ($ward->beds->count() >= $ward->capacity) {
            return response()->json([
                'message' => 'Ward capacity is full'
            ], 400);
        }

        $bed = Bed::create([
            'ward_id' => $request->ward_id,
            'bed_number' => $request->bed_number,
        ]);

        return response()->json(['message' => 'Bed added', 'data' => $bed], 201);
    }

    // Update Bed Status
    public function update(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:available,occupied,maintenance']);

        $bed = Bed::findOrFail($id);
        $bed->update($request->only('status'));

        return response()->json(['message' => 'Bed updated']);
    }

    // Remove Bed
    public function destroy($id)
    {
        Bed::findOrFail($id)->delete();

        return response()->json(['message' => 'Bed removed']);
    }
}
