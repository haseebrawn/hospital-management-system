<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DoctorAvailability;
use Carbon\Carbon;

class DoctorAvailabilityService
{
    public function isAvailable(int $doctorId, string $date, string $time): bool
    {
        $appointmentDate = Carbon::parse($date);
        $appointmentTime = Carbon::parse($time)->format('H:i:s');

        return DoctorAvailability::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $appointmentDate->dayOfWeek)
            ->where('is_active', true)
            ->where('start_time', '<=', $appointmentTime)
            ->where('end_time', '>=', $appointmentTime)
            ->exists();
    }

    public function hasBookingConflict(int $doctorId, string $date, string $time, ?int $ignoreAppointmentId = null): bool
    {
        $appointmentTime = Carbon::parse($time)->format('H:i:s');
        $appointmentTimeShort = Carbon::parse($time)->format('H:i');

        return Appointment::query()
            ->where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where(function ($query) use ($appointmentTime, $appointmentTimeShort) {
                $query->where('time', $appointmentTime)
                    ->orWhere('time', $appointmentTimeShort);
            })
            ->where('status', '!=', 'cancelled')
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->exists();
    }
}
