<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\BedAllocationAssignRequest;
use App\Http\Requests\Web\BedAllocationTransferRequest;
use App\Http\Requests\Web\BedStoreRequest;
use App\Http\Requests\Web\BedUpdateRequest;
use App\Http\Requests\Web\WardStoreRequest;
use App\Http\Requests\Web\WardUpdateRequest;
use App\Models\Bed;
use App\Models\BedAllocation;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WardsBedsController extends Controller
{
    public function index()
    {
        return view('modules.wards-beds.index');
    }

    /*
    |--------------------------------------------------------------------------
    | Wards
    |--------------------------------------------------------------------------
    */

    public function wardsIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $departmentId = trim((string) $request->query('department_id', ''));

        $wards = Ward::query()
            ->with(['department'])
            ->withCount('beds')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($departmentId !== '', fn ($q) => $q->where('department_id', $departmentId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::query()->orderBy('name')->get();

        return view('modules.wards-beds.wards.index', compact('wards', 'search', 'departmentId', 'departments'));
    }

    public function wardsCreate()
    {
        $departments = Department::query()->orderBy('name')->get();

        return view('modules.wards-beds.wards.create', compact('departments'));
    }

    public function wardsStore(WardStoreRequest $request)
    {
        Ward::create($request->validated());

        return redirect()
            ->route('wards.index')
            ->with('status', 'Ward created successfully.');
    }

    public function wardsEdit(Ward $ward)
    {
        $departments = Department::query()->orderBy('name')->get();

        return view('modules.wards-beds.wards.edit', compact('ward', 'departments'));
    }

    public function wardsUpdate(WardUpdateRequest $request, Ward $ward)
    {
        $ward->update($request->validated());

        return redirect()
            ->route('wards.index')
            ->with('status', 'Ward updated successfully.');
    }

    public function wardsDestroy(Ward $ward)
    {
        $ward->delete();

        return redirect()
            ->route('wards.index')
            ->with('status', 'Ward deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Beds
    |--------------------------------------------------------------------------
    */

    public function bedsIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $wardId = trim((string) $request->query('ward_id', ''));
        $status = trim((string) $request->query('status', ''));

        $beds = Bed::query()
            ->with(['ward.department'])
            ->when($wardId !== '', fn ($q) => $q->where('ward_id', $wardId))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('bed_number', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $wards = Ward::query()->orderBy('name')->get();
        $statusOptions = ['available', 'occupied', 'maintenance'];

        return view('modules.wards-beds.beds.index', compact('beds', 'search', 'wardId', 'status', 'wards', 'statusOptions'));
    }

    public function bedsCreate()
    {
        $wards = Ward::query()->orderBy('name')->get();
        $statusOptions = ['available', 'occupied', 'maintenance'];

        return view('modules.wards-beds.beds.create', compact('wards', 'statusOptions'));
    }

    public function bedsStore(BedStoreRequest $request)
    {
        $data = $request->validated();

        $ward = Ward::withCount('beds')->findOrFail($data['ward_id']);
        if ($ward->beds_count >= (int) $ward->capacity) {
            return back()
                ->withErrors(['ward_id' => 'Ward capacity is full.'])
                ->withInput();
        }

        Bed::create($data);

        return redirect()
            ->route('beds.index')
            ->with('status', 'Bed created successfully.');
    }

    public function bedsEdit(Bed $bed)
    {
        $wards = Ward::query()->orderBy('name')->get();
        $statusOptions = ['available', 'occupied', 'maintenance'];

        return view('modules.wards-beds.beds.edit', compact('bed', 'wards', 'statusOptions'));
    }

    public function bedsUpdate(BedUpdateRequest $request, Bed $bed)
    {
        $bed->update($request->validated());

        return redirect()
            ->route('beds.index')
            ->with('status', 'Bed updated successfully.');
    }

    public function bedsDestroy(Bed $bed)
    {
        $bed->delete();

        return redirect()
            ->route('beds.index')
            ->with('status', 'Bed deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Allocations
    |--------------------------------------------------------------------------
    */

    public function allocationsIndex(Request $request)
    {
        $patientId = trim((string) $request->query('patient_id', ''));
        $bedId = trim((string) $request->query('bed_id', ''));
        $active = trim((string) $request->query('active', ''));

        $allocations = BedAllocation::query()
            ->with(['patient', 'bed.ward'])
            ->when($patientId !== '', fn ($q) => $q->where('patient_id', $patientId))
            ->when($bedId !== '', fn ($q) => $q->where('bed_id', $bedId))
            ->when($active !== '', function ($q) use ($active) {
                if ($active === '1') {
                    $q->whereNull('released_at');
                } elseif ($active === '0') {
                    $q->whereNotNull('released_at');
                }
            })
            ->orderByDesc('assigned_at')
            ->paginate(15)
            ->withQueryString();

        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $beds = Bed::query()->with('ward')->orderByDesc('id')->limit(200)->get();

        return view('modules.wards-beds.allocations.index', compact('allocations', 'patientId', 'bedId', 'active', 'patients', 'beds'));
    }

    public function allocationsCreate()
    {
        $patients = Patient::query()->orderByDesc('id')->limit(300)->get();
        $beds = Bed::query()->with('ward')->where('status', 'available')->orderByDesc('id')->limit(300)->get();

        return view('modules.wards-beds.allocations.create', compact('patients', 'beds'));
    }

    public function allocationsStore(BedAllocationAssignRequest $request)
    {
        $data = $request->validated();

        $allocation = DB::transaction(function () use ($data) {
            $bed = Bed::lockForUpdate()->findOrFail($data['bed_id']);

            if ($bed->status !== 'available') {
                abort(422, 'Bed is not available.');
            }

            $allocation = BedAllocation::create([
                'patient_id' => $data['patient_id'],
                'bed_id' => $data['bed_id'],
                'assigned_at' => Carbon::now(),
                'released_at' => null,
            ]);

            $bed->update(['status' => 'occupied']);

            return $allocation;
        });

        return redirect()
            ->route('allocations.index')
            ->with('status', "Bed assigned successfully (Allocation #{$allocation->id}).");
    }

    public function allocationsRelease(BedAllocation $allocation)
    {
        if ($allocation->released_at) {
            return redirect()
                ->route('allocations.index')
                ->with('status', 'Allocation is already released.');
        }

        DB::transaction(function () use ($allocation) {
            $allocation->update(['released_at' => Carbon::now()]);
            $allocation->bed->update(['status' => 'available']);
        });

        return redirect()
            ->route('allocations.index')
            ->with('status', 'Bed released successfully.');
    }

    public function allocationsTransfer(BedAllocationTransferRequest $request, BedAllocation $allocation)
    {
        if ($allocation->released_at) {
            return redirect()
                ->route('allocations.index')
                ->with('status', 'Released allocations cannot be transferred.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($allocation, $data) {
            $newBed = Bed::lockForUpdate()->findOrFail($data['bed_id']);
            if ($newBed->status !== 'available') {
                abort(422, 'New bed is not available.');
            }

            $allocation->update(['released_at' => Carbon::now()]);
            $allocation->bed->update(['status' => 'available']);

            BedAllocation::create([
                'patient_id' => $allocation->patient_id,
                'bed_id' => $newBed->id,
                'assigned_at' => Carbon::now(),
                'released_at' => null,
            ]);

            $newBed->update(['status' => 'occupied']);
        });

        return redirect()
            ->route('allocations.index')
            ->with('status', 'Bed transferred successfully.');
    }
}
