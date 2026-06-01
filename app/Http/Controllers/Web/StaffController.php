<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StaffStoreRequest;
use App\Http\Requests\Web\StaffUpdateRequest;
use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $departmentId = trim((string) $request->query('department_id', ''));
        $status = trim((string) $request->query('status', ''));

        $staff = Staff::query()
            ->with(['user', 'department'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($departmentId !== '', fn ($q) => $q->where('department_id', $departmentId))
            ->when($status !== '', fn ($q) => $q->where('employment_status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::query()->orderBy('name')->get();
        $statusOptions = ['active', 'terminated', 'resigned'];

        return view('modules.staff.index', compact('staff', 'search', 'departmentId', 'status', 'departments', 'statusOptions'));
    }

    public function create()
    {
        $departments = Department::query()->orderBy('name')->get();
        $statusOptions = ['active', 'terminated', 'resigned'];

        $users = User::query()
            ->whereDoesntHave('staff')
            ->orderBy('name')
            ->limit(300)
            ->get();

        return view('modules.staff.create', compact('departments', 'statusOptions', 'users'));
    }

    public function store(StaffStoreRequest $request)
    {
        $staff = Staff::create($request->validated());

        return redirect()
            ->route('staff.show', $staff)
            ->with('status', 'Staff profile created successfully.');
    }

    public function show(Staff $staff)
    {
        $staff->load(['user', 'department', 'shifts']);

        return view('modules.staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        $staff->load(['user', 'department']);

        $departments = Department::query()->orderBy('name')->get();
        $statusOptions = ['active', 'terminated', 'resigned'];

        return view('modules.staff.edit', compact('staff', 'departments', 'statusOptions'));
    }

    public function update(StaffUpdateRequest $request, Staff $staff)
    {
        $staff->update($request->validated());

        return redirect()
            ->route('staff.show', $staff)
            ->with('status', 'Staff profile updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff profile deleted successfully.');
    }
}
