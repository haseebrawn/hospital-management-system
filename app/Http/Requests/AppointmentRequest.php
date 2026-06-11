<?php

namespace App\Http\Requests;

use App\Services\DoctorAvailabilityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (!$user) {
            return false;
        }

        return $user->hasAnyRole([
            'super_admin',
            'admin',
            'receptionist'
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:scheduled,completed,cancelled',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $doctorId = $this->input('doctor_id');
            $scheduledAt = $this->input('scheduled_at');

            if (! $doctorId || ! $scheduledAt) {
                return;
            }

            try {
                $scheduled = \Carbon\Carbon::parse($scheduledAt);
            } catch (\Throwable) {
                return;
            }
            $availabilityService = app(DoctorAvailabilityService::class);

            $isAvailable = $availabilityService->isAvailable(
                (int) $doctorId,
                $scheduled->toDateString(),
                $scheduled->format('H:i')
            );

            if (! $isAvailable) {
                $validator->errors()->add('doctor_id', 'Selected doctor is not available at this appointment date and time.');
                return;
            }

            $hasConflict = $availabilityService->hasBookingConflict(
                (int) $doctorId,
                $scheduled->toDateString(),
                $scheduled->format('H:i'),
                $this->route('id') ? (int) $this->route('id') : null
            );

            if ($hasConflict) {
                $validator->errors()->add('doctor_id', 'Selected doctor already has an active appointment at this date and time.');
            }
        });
    }
}
