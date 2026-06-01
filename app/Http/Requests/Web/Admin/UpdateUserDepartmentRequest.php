<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];
    }
}

