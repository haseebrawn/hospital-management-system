<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedAllocation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BedAllocationController extends Controller
{
    //  Assign Bed
    public function assign(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'bed_id' => 'required|exists:beds,id'
        ]);

        $bed = Bed::findOrFail($request->bed_id);

        if ($bed->status != 'available') {
            return response()->json([
                'message' => 'Bed is not available'
            ], 400);
        }

        $allocation = BedAllocation::create([
            'patient_id' => $request->patient_id,
            'bed_id' => $request->bed_id,
            'assigned_at' => Carbon::now()
        ]);

        $bed->update(['status' => 'occupied']);

        return response()->json([
            'message' => 'Bed assigned successfully',
            'data' => $allocation
        ], 201);
    }

    //  Release Bed
    public function release($id)
    {
        $allocation = BedAllocation::findOrFail($id);

        $allocation->update([
            'released_at' => Carbon::now()
        ]);

        $allocation->bed->update(['status' => 'available']);

        return response()->json(['message' => 'Bed released']);
    }

    //  Transfer Bed
    public function transfer(Request $request, $id)
    {
        $request->validate(['bed_id' => 'required|exists:beds,id']);

        $allocation = BedAllocation::findOrFail($id);
        $newBed = Bed::findOrFail($request->bed_id);

        if ($newBed->status != 'available') {
            return response()->json(['message' => 'New bed not available'], 400);
        }

        // End previous allocation
        $allocation->update(['released_at' => Carbon::now()]);
        $allocation->bed->update(['status' => 'available']);

        // Create new allocation
        $newAllocation = BedAllocation::create([
            'patient_id' => $allocation->patient_id,
            'bed_id' => $newBed->id,
            'assigned_at' => Carbon::now()
        ]);

        $newBed->update(['status' => 'occupied']);

        return response()->json([
            'message' => 'Bed transferred',
            'data' => $newAllocation
        ]);
    }
}
