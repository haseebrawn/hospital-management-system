<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LabTestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            'super_admin',
            'admin',
            'lab_technician',
            'doctor',
        ]);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'lab_technician_id' => ['required', 'integer', 'exists:users,id'],
            'test_type' => ['required', 'string', 'max:255'],
            'results' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_process,completed'],
        ];
    }
}
