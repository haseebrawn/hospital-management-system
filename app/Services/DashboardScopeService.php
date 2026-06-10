<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Billing;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;

class DashboardScopeService
{
    public function visibility(User $user): array
    {
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);

        return [
            'patients' => $isAdmin || $user->hasAnyRole(['doctor', 'nurse', 'receptionist']),
            'appointments' => $isAdmin || $user->hasAnyRole(['doctor', 'nurse', 'receptionist']),
            'lab_tests' => $isAdmin || $user->hasAnyRole(['doctor', 'lab_technician']),
            'revenue' => $isAdmin || $user->hasRole('accountant'),
            'beds' => $isAdmin || $user->hasAnyRole(['doctor', 'nurse']),
            'staff' => $isAdmin || $user->hasRole('hr_manager'),
            'pharmacy' => $isAdmin || $user->hasRole('pharmacist'),
        ];
    }

    public function patients(User $user)
    {
        $query = Patient::query();

        if ($this->canSeeAll($user)) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    public function appointments(User $user)
    {
        $query = Appointment::query();

        if ($this->canSeeAll($user)) {
            return $query;
        }

        if ($user->hasRole('doctor')) {
            return $query->where(function ($appointmentQuery) use ($user) {
                $appointmentQuery->where('doctor_id', $user->id)
                    ->orWhere('department_id', $user->department_id);
            });
        }

        return $query->where('department_id', $user->department_id);
    }

    public function labTests(User $user)
    {
        $query = LabTest::query();

        if ($this->canSeeAll($user)) {
            return $query;
        }

        if ($user->hasRole('lab_technician')) {
            return $query->where('lab_technician_id', $user->id);
        }

        if ($user->hasRole('doctor')) {
            return $query->where('doctor_id', $user->id);
        }

        return $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $user->department_id));
    }

    public function billings(User $user)
    {
        $query = Billing::query();

        if ($this->canSeeAll($user)) {
            return $query;
        }

        if ($user->hasRole('accountant')) {
            return $query->where(function ($billingQuery) use ($user) {
                $billingQuery->where('created_by', $user->id)
                    ->orWhere('approved_by', $user->id);
            });
        }

        return $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $user->department_id));
    }

    public function beds(User $user)
    {
        $query = Bed::query();

        if ($this->canSeeAll($user)) {
            return $query;
        }

        return $query->whereHas('ward', fn ($wardQuery) => $wardQuery->where('department_id', $user->department_id));
    }

    public function staff(User $user)
    {
        $query = Staff::query();

        if ($this->canSeeAll($user)) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    public function canSeeAll(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
