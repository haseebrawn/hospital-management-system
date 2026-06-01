<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BedAllocationTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'nurse', 'doctor']);
    }

    public function rules(): array
    {
        return [
            'bed_id' => ['required', 'integer', 'exists:beds,id'],
        ];
    }
}

