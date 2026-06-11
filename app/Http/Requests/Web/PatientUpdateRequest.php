<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PatientUpdateRequest extends FormRequest
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
            'mrn' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('patients', 'mrn')->ignore($this->route('patient')),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];
    }
}
