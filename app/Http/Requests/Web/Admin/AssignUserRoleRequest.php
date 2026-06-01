<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where(fn ($q) => $q->where('guard_name', 'api')),
            ],
        ];
    }
}
