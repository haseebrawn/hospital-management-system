<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest; // we will create this
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        $departments = Department::orderBy('name')->pluck('name');

        return view('auth.register', compact('departments'));
    }

    public function register(RegisterRequest $request)
    {
        // validated data from RegisterRequest
        $data = $request->validated();

        $department = Department::where('name', $data['department'])->first();
        if (! $department) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Invalid department'], 422);
            }

            return back()
                ->withErrors(['department' => 'Invalid department'])
                ->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department_id' => $department->id,
        ]);

        // role fallback: if role given and exists -> assign, else assign department default
        $roleName = $data['role'] ?? strtolower($department->name) . '_staff';

        // ensure role exists: if not, create (or you can require role creation elsewhere)
        if (! \Spatie\Permission\Models\Role::where(['name' => $roleName, 'guard_name' => 'api'])->exists()) {
            \Spatie\Permission\Models\Role::create([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
        }

        $user->assignRole($roleName);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'User registered successfully.',
                'redirect' => route('login'),
                'user' => new UserResource($user->load('roles')),
                'assigned_role' => $roleName,
                'assigned_department' => $department,
            ], 201);
        }

        return redirect()->route('login')
            ->with('status', 'User registered successfully. Please log in.');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // === API login ONLY ===
        if ($request->is('api/*')) {
            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => new UserResource($user->load('roles')),
            ]);
        }

        // === Web login (session-based) ===
        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        if (!Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The provided credentials do not match our records.',
                ], 422);
            }

            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->withInput($request->only('email', 'remember'));
        }

        // regenerate session
        $request->session()->regenerate();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized - Invalid or expired token'
            ], 401);
        }

        return response()->json(
            new UserResource($user->load('roles', 'department'))
        );
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // API logout
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        }

        // Web logout: force web guard
        Auth::guard('web')->logout();  // ← use web guard explicitly
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard()
    {
        return view('dashboard');
    }
}
