<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AppointmentStoreRequest extends FormRequest
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
            'doctor',
            'nurse',
            'receptionist',
        ]);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'status' => ['required', 'in:pending,approved,completed,cancelled'],
        ];
    }
}

