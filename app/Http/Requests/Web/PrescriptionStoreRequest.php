<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PrescriptionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user?->hasAnyRole(['super_admin', 'admin', 'doctor']) ?? false;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['required', 'string', 'max:5000'],
            'medicines' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['pending', 'dispensed', 'cancelled'])],
        ];
    }
}
