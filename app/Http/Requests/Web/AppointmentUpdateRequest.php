<?php

namespace App\Http\Requests\Web;

use App\Services\DoctorAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class AppointmentUpdateRequest extends FormRequest
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
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:pending,approved,completed,cancelled'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $doctorId = $this->input('doctor_id');

            if (! $doctorId || ! $this->filled(['date', 'time'])) {
                return;
            }

            $availabilityService = app(DoctorAvailabilityService::class);

            $isAvailable = $availabilityService->isAvailable(
                (int) $doctorId,
                (string) $this->input('date'),
                (string) $this->input('time')
            );

            if (! $isAvailable) {
                $validator->errors()->add('doctor_id', 'Selected doctor is not available at this appointment date and time.');
                return;
            }

            $hasConflict = $availabilityService->hasBookingConflict(
                (int) $doctorId,
                (string) $this->input('date'),
                (string) $this->input('time'),
                $this->route('appointment')?->id
            );

            if ($hasConflict) {
                $validator->errors()->add('doctor_id', 'Selected doctor already has an active appointment at this date and time.');
            }
        });
    }
}
