<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StaffStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'hr_manager']);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:staff,user_id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'designation' => ['required', 'string', 'max:255'],
            'salary' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['required', 'date'],
            'employment_status' => ['required', 'in:active,terminated,resigned'],
        ];
    }
}

