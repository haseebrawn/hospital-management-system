<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MedicalRecordStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'admin', 'doctor']) ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'visit_type' => ['required', Rule::in(['consultation', 'follow_up', 'emergency', 'admission', 'discharge'])],
            'chief_complaint' => ['nullable', 'string', 'max:255'],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'vitals' => ['nullable', 'string', 'max:2000'],
            'history' => ['nullable', 'string', 'max:5000'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }
}
