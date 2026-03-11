<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    // Assign Shift
    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff,id',
            'shift_name' => 'required|in:Morning,Evening,Night',
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i',
            'shift_date' => 'required|date',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $shift = StaffShift::create($validator->validated());
        return response()->json(['message' => 'Shift Assigned', 'shift' => $shift], 201);
    }

    // List Shifts (Filtered)
    public function index(Request $request)
    {
        $query = StaffShift::with('staff.user');

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->shift_date) {
            $query->where('shift_date', $request->shift_date);
        }

        return response()->json($query->paginate(10));
    }
}
