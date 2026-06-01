<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ShiftAssignRequest;
use App\Models\Staff;
use App\Models\StaffShift;
use Illuminate\Http\Request;

class ShiftsController extends Controller
{
    public function index(Request $request)
    {
        $staffId = trim((string) $request->query('staff_id', ''));
        $shiftDate = trim((string) $request->query('shift_date', ''));

        $shifts = StaffShift::query()
            ->with(['staff.user', 'staff.department'])
            ->when($staffId !== '', fn ($q) => $q->where('staff_id', $staffId))
            ->when($shiftDate !== '', fn ($q) => $q->where('shift_date', $shiftDate))
            ->orderByDesc('shift_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $staffOptions = Staff::query()
            ->with('user')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('modules.shifts.index', compact('shifts', 'staffOptions', 'staffId', 'shiftDate'));
    }

    public function create()
    {
        $staffOptions = Staff::query()
            ->with(['user', 'department'])
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $shiftNameOptions = ['Morning', 'Evening', 'Night'];

        return view('modules.shifts.create', compact('staffOptions', 'shiftNameOptions'));
    }

    public function store(ShiftAssignRequest $request)
    {
        StaffShift::create($request->validated());

        return redirect()
            ->route('shifts.index')
            ->with('status', 'Shift assigned successfully.');
    }
}
