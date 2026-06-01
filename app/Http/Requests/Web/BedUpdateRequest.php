<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BedUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'nurse']);
    }

    public function rules(): array
    {
        return [
            'ward_id' => ['required', 'integer', 'exists:wards,id'],
            'bed_number' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:available,occupied,maintenance'],
        ];
    }
}

