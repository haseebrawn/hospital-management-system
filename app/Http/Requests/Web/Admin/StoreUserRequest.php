<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function rules(): array
    {
        $actor = Auth::user();
        $allowedRoles = ['user', 'doctor', 'nurse', 'receptionist', 'lab_technician', 'pharmacist', 'accountant', 'hr_manager'];

        if ($actor?->hasRole('super_admin')) {
            $allowedRoles = array_merge(['super_admin', 'admin'], $allowedRoles);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
                function (string $attribute, mixed $value, \Closure $fail) use ($actor) {
                    if ($actor?->hasRole('admin') && (int) $value !== (int) $actor->department_id) {
                        $fail('Department admin can create users only in their own department.');
                    }
                },
            ],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'create_staff_profile' => ['nullable', 'boolean'],
            'designation' => ['required_if:create_staff_profile,1', 'nullable', 'string', 'max:255'],
            'salary' => ['required_if:create_staff_profile,1', 'nullable', 'numeric', 'min:0'],
            'joining_date' => ['required_if:create_staff_profile,1', 'nullable', 'date'],
            'employment_status' => ['required_if:create_staff_profile,1', 'nullable', Rule::in(['active', 'terminated', 'resigned'])],
        ];
    }
}
