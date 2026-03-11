<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartmentAdmin
{
    /**
     * Allow access if:
     * - user is super_admin OR
     * - user has role 'admin' and target resource belongs to same department
     *
     * This middleware expects a route param 'id' (user id) OR 'department' (department name/slug) when applicable.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Super admin can do everything
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // If the user is not a department-level admin, deny
        if (! $user->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden - requires admin or super_admin'], 403);
        }

        // If route supplies a user id, enforce that it belongs to the same department
        if ($request->route('id')) {
            $targetUserId = $request->route('id');
            $targetUser = \App\Models\User::with('department')->find($targetUserId);

            if (! $targetUser) {
                return response()->json(['message' => 'Target user not found'], 404);
            }

            if ($targetUser->department_id !== $user->department_id) {
                return response()->json(['message' => 'Forbidden - cannot manage users from other departments'], 403);
            }

            return $next($request);
        }

        // If route supplies a department param (by name)
        if ($request->route('department')) {
            $deptParam = $request->route('department');
            $dept = \App\Models\Department::where('name', $deptParam)
                ->orWhere('slug', $deptParam)
                ->first();

            if (! $dept) {
                return response()->json(['message' => 'Department not found'], 404);
            }

            if ($dept->id !== $user->department_id) {
                return response()->json(['message' => 'Forbidden - cannot manage other departments'], 403);
            }

            return $next($request);
        }

        // Fallback: allow only if same department route exists or deny
        return response()->json(['message' => 'Forbidden - missing route parameters for department check'], 403);
    }
}
