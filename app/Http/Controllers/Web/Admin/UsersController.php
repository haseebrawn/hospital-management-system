<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\AssignUserRoleRequest;
use App\Http\Requests\Web\Admin\UpdateUserDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with(['department', 'roles'])
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::query()
            ->where('guard_name', 'api')
            ->orderBy('name')
            ->get();

        $departments = Department::query()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function assignRole(AssignUserRoleRequest $request, User $user)
    {
        $roleName = $request->validated('role');
        $user->assignRole($roleName);

        return back()->with('status', 'Role assigned successfully.');
    }

    public function removeRole(User $user)
    {
        $role = request()->input('role');
        if ($role) {
            $user->removeRole($role);
        } else {
            $user->syncRoles([]);
        }

        return back()->with('status', 'Role removed successfully.');
    }

    public function updateDepartment(UpdateUserDepartmentRequest $request, User $user)
    {
        $user->update([
            'department_id' => $request->validated('department_id'),
        ]);

        return back()->with('status', 'Department updated successfully.');
    }
}
