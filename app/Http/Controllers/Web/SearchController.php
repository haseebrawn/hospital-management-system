<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Billing;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 1) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => collect()
                ->merge($this->patients($query))
                ->merge($this->appointments($query))
                ->merge($this->labTests($query))
                ->merge($this->medicines($query))
                ->merge($this->billing($query))
                ->merge($this->staff($query))
                ->merge($this->beds($query))
                ->take(10)
                ->values(),
        ]);
    }

    private function patients(string $query)
    {
        return Patient::query()
            ->where('mrn', 'like', "%{$query}%")
            ->orWhere('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('contact_number', 'like', "%{$query}%")
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Patient $patient) => [
                'title' => trim("{$patient->first_name} {$patient->last_name}"),
                'subtitle' => "Patient • {$patient->mrn} • {$patient->contact_number}",
                'icon' => 'fa-solid fa-user-plus',
                'url' => route('patients.show', $patient),
            ]);
    }

    private function appointments(string $query)
    {
        return Appointment::query()
            ->with('patient')
            ->where('status', 'like', "%{$query}%")
            ->orWhere('date', 'like', "%{$query}%")
            ->orWhere('reason', 'like', "%{$query}%")
            ->orWhere('notes', 'like', "%{$query}%")
            ->orWhereHas('patient', function ($patientQuery) use ($query) {
                $patientQuery->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (Appointment $appointment) => [
                'title' => optional($appointment->patient)->first_name
                    ? trim(optional($appointment->patient)->first_name . ' ' . optional($appointment->patient)->last_name)
                    : "Appointment #{$appointment->id}",
                'subtitle' => "Appointment • {$appointment->date} {$appointment->time} • {$appointment->status}",
                'icon' => 'fa-solid fa-calendar-check',
                'url' => route('appointments.show', $appointment),
            ]);
    }

    private function labTests(string $query)
    {
        return LabTest::query()
            ->with('patient')
            ->where('test_type', 'like', "%{$query}%")
            ->orWhere('status', 'like', "%{$query}%")
            ->orWhereHas('patient', function ($patientQuery) use ($query) {
                $patientQuery->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (LabTest $labTest) => [
                'title' => $labTest->test_type,
                'subtitle' => 'Lab Test • ' . (optional($labTest->patient)->first_name ?? 'Patient') . " • {$labTest->status}",
                'icon' => 'fa-solid fa-flask',
                'url' => route('lab-tests.show', $labTest),
            ]);
    }

    private function medicines(string $query)
    {
        return Medicine::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('status', 'like', "%{$query}%")
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (Medicine $medicine) => [
                'title' => $medicine->name,
                'subtitle' => "Medicine • Stock {$medicine->stock} • {$medicine->status}",
                'icon' => 'fa-solid fa-pills',
                'url' => route('medicines.show', $medicine),
            ]);
    }

    private function billing(string $query)
    {
        return Billing::query()
            ->with('patient')
            ->where('status', 'like', "%{$query}%")
            ->orWhere('total_amount', 'like', "%{$query}%")
            ->orWhereHas('patient', function ($patientQuery) use ($query) {
                $patientQuery->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (Billing $billing) => [
                'title' => 'Invoice #' . $billing->id,
                'subtitle' => "Billing • {$billing->total_amount} • {$billing->status}",
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'url' => route('billing.show', $billing),
            ]);
    }

    private function staff(string $query)
    {
        return Staff::query()
            ->with('user')
            ->where('designation', 'like', "%{$query}%")
            ->orWhere('employment_status', 'like', "%{$query}%")
            ->orWhereHas('user', function ($userQuery) use ($query) {
                $userQuery->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (Staff $staff) => [
                'title' => optional($staff->user)->name ?? "Staff #{$staff->id}",
                'subtitle' => "Staff • {$staff->designation} • {$staff->employment_status}",
                'icon' => 'fa-solid fa-user-doctor',
                'url' => route('staff.show', $staff),
            ]);
    }

    private function beds(string $query)
    {
        return Bed::query()
            ->with('ward')
            ->where('bed_number', 'like', "%{$query}%")
            ->orWhere('status', 'like', "%{$query}%")
            ->orWhereHas('ward', fn ($wardQuery) => $wardQuery->where('name', 'like', "%{$query}%"))
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (Bed $bed) => [
                'title' => "Bed {$bed->bed_number}",
                'subtitle' => 'Ward & Bed • ' . (optional($bed->ward)->name ?? 'Ward') . " • {$bed->status}",
                'icon' => 'fa-solid fa-bed',
                'url' => route('beds.edit', $bed),
            ]);
    }
}
