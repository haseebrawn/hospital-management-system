<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\AssignUserRoleRequest;
use App\Http\Requests\Web\Admin\StoreUserRequest;
use App\Http\Requests\Web\Admin\UpdateUserDepartmentRequest;
use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $actor = request()->user();

        $users = User::query()
            ->with(['department', 'roles'])
            ->when(! $actor->hasRole('super_admin'), function ($query) use ($actor) {
                $query->where('department_id', $actor->department_id)
                    ->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['super_admin', 'admin']);
                    });
            })
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::query()
            ->where('guard_name', 'api')
            ->whereNotIn('name', ['view logs', 'manage backups', 'manage security', 'view backups'])
            ->when(! $actor->hasRole('super_admin'), function ($query) {
                $query->whereNotIn('name', ['super_admin', 'admin']);
            })
            ->orderBy('name')
            ->get();

        $departments = Department::query()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        $actor = request()->user();

        $roles = Role::query()
            ->where('guard_name', 'api')
            ->whereNotIn('name', ['view logs', 'manage backups', 'manage security', 'view backups'])
            ->when(! $actor->hasRole('super_admin'), function ($query) {
                $query->whereNotIn('name', ['super_admin', 'admin']);
            })
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->when($actor->hasRole('admin') && ! $actor->hasRole('super_admin'), fn ($query) => $query->whereKey($actor->department_id))
            ->orderBy('name')
            ->get();

        $statusOptions = ['active', 'terminated', 'resigned'];

        return view('admin.users.create', compact('roles', 'departments', 'statusOptions'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department_id' => $data['department_id'],
        ]);

        $user->assignRole($data['role']);

        if ($request->boolean('create_staff_profile')) {
            Staff::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'designation' => $data['designation'],
                'salary' => $data['salary'],
                'joining_date' => $data['joining_date'],
                'employment_status' => $data['employment_status'],
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    public function assignRole(AssignUserRoleRequest $request, User $user)
    {
        $this->authorizeDepartmentAdminAction($user, $request->validated('role'));

        $roleName = $request->validated('role');
        $user->assignRole($roleName);

        return back()->with('status', 'Role assigned successfully.');
    }

    public function removeRole(User $user)
    {
        $this->authorizeDepartmentAdminAction($user, request()->input('role'));

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

    private function authorizeDepartmentAdminAction(User $targetUser, ?string $roleName = null): void
    {
        $actor = request()->user();

        if ($actor->hasRole('super_admin')) {
            return;
        }

        abort_unless($actor->hasRole('admin'), 403);
        abort_unless($targetUser->department_id === $actor->department_id, 403);
        abort_if($targetUser->hasAnyRole(['super_admin', 'admin']), 403);
        abort_if(in_array($roleName, ['super_admin', 'admin'], true), 403);
    }
}
