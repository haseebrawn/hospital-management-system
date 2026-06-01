<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShiftAssignRequest extends FormRequest
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
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'shift_name' => ['required', 'in:Morning,Evening,Night'],
            'shift_start' => ['required', 'date_format:H:i'],
            'shift_end' => ['required', 'date_format:H:i'],
            'shift_date' => ['required', 'date'],
        ];
    }
}

