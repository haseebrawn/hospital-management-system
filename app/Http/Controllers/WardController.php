<?php

namespace App\Http\Controllers;

use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WardController extends Controller
{
    // List Wards — filtered by Nurse Department
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ward::with('beds');

        if ($user->hasRole('nurse')) {
            $query->where('department_id', $user->department_id);
        }

        return response()->json($query->get());
    }

    // Create Ward (Admin/Super Admin)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'capacity' => 'required|integer|min:1'
        ]);

        $ward = Ward::create($request->all());

        return response()->json([
            'message' => 'Ward created successfully',
            'data' => $ward
        ], 201);
    }

    // Update Ward
    public function update(Request $request, $id)
    {
        $ward = Ward::findOrFail($id);

        $ward->update($request->only(['name', 'capacity']));

        return response()->json([
            'message' => 'Ward updated',
            'data' => $ward
        ]);
    }

    // Delete Ward
    public function destroy($id)
    {
        Ward::findOrFail($id)->delete();

        return response()->json(['message' => 'Ward deleted']);
    }
}
