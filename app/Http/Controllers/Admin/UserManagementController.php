<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{

    /**
     * List users.
     * - super_admin sees all users
     * - department admin sees users from their department only
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $users = User::with('department', 'roles')->paginate(20);
        } else if ($user->hasRole('admin')) {
            $users = User::with('department', 'roles')
                ->where('department_id', $user->department_id)
                ->paginate(20);
        } else {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($users);
    }

    /**
     * Assign a role to a user. Use AssignRoleRequest.
     * - super_admin can assign any role
     * - department admin can assign roles only to users in same department and should not assign roles from other departments ideally
     */
    public function assignRole(AssignRoleRequest $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $roleName = $request->input('role');

        $role = Role::findByName($roleName);
        if (! $role) {
            return response()->json([
                'message' => "Role '{$roleName}' not found for guard api"
            ], 422);
        }

        //  Force assign role to API guard
        // $user->roles()->detach();
        $user->assignRole($roleName);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles')
        ]);
    }


    /**
     * Change a user's department (only super_admin allowed)
     */
    public function updateDepartment(UpdateDepartmentRequest $request, $id)
    {
        $actor = $request->user();
        if (! $actor->hasRole('super_admin')) {
            return response()->json(['message' => 'Forbidden - only super_admin can change departments'], 403);
        }

        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $deptValue = $request->input('department');
        $department = Department::where('name', $deptValue)->orWhere('slug', $deptValue)->first();

        $user->department_id = $department->id;
        $user->save();

        return response()->json([
            'message' => 'Department updated successfully',
            'user' => $user->load('department', 'roles')
        ]);
    }

    /**
     * Remove all roles (or a single role) from a user
     */
    public function removeRole(Request $request, $id)
    {
        $actor = $request->user();
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($actor->hasRole('admin') && $actor->department_id !== $user->department_id && ! $actor->hasRole('super_admin')) {
            return response()->json(['message' => 'Forbidden - cannot modify users from other departments'], 403);
        }

        // If role param passed, remove only that role; otherwise remove all roles
        $role = $request->input('role');
        if ($role) {
            $user->removeRole($role);
        } else {
            $user->syncRoles([]); // remove all
        }

        return response()->json([
            'message' => 'Role(s) removed',
            'user' => $user->load('roles', 'department')
        ]);
    }
}
