<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HRController extends Controller
{
    // Filtered Staff Listing
    public function index(Request $request)
    {
        $query = Staff::with('user', 'department');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->designation) {
            $query->where('designation', $request->designation);
        }

        if ($request->status) {
            $query->where('employment_status', $request->status);
        }

        return response()->json($query->paginate(10));
    }

    // Create Staff Profile
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:staff,user_id',
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'joining_date' => 'required|date',
            'employment_status' => 'required|in:active,terminated,resigned',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $staff = Staff::create($validator->validated());
        return response()->json(['message' => 'Staff Added', 'staff' => $staff], 201);
    }

    // Update Staff
    public function update(Request $request, $id)
    {
        $staff = Staff::find($id);
        if (! $staff)
            return response()->json(['message' => 'Staff Not Found'], 404);

        $staff->update($request->only(['designation', 'salary', 'employment_status']));
        return response()->json(['message' => 'Staff Updated', 'staff' => $staff]);
    }

    // Delete Staff
    public function destroy($id)
    {
        $staff = Staff::find($id);
        if (! $staff)
            return response()->json(['message' => 'Staff Not Found'], 404);

        $staff->delete();
        return response()->json(['message' => 'Staff Removed']);
    }
}
