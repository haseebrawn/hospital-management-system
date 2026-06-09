<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AppointmentStoreRequest;
use App\Http\Requests\Web\AppointmentUpdateRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'department'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = ['pending', 'approved', 'completed', 'cancelled'];

        return view('modules.appointments.index', compact('appointments', 'search', 'status', 'statusOptions'));
    }

    public function create()
    {
        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $doctors = User::query()->role('doctor')->orderBy('name')->get();
        $departments = Department::query()->orderBy('name')->get();
        $statusOptions = ['pending', 'approved', 'completed', 'cancelled'];

        return view('modules.appointments.create', compact('patients', 'doctors', 'departments', 'statusOptions'));
    }

    public function store(AppointmentStoreRequest $request, HospitalNotificationService $notifications)
    {
        $appointment = Appointment::create($request->validated());
        $appointment->load(['patient', 'doctor']);

        $patientName = trim((string) (($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '')));

        $payload = [
            'title' => 'New appointment created',
            'message' => "{$patientName} has a {$appointment->status} appointment on {$appointment->date} at {$appointment->time}.",
            'module' => 'appointments',
            'type' => 'success',
            'url' => route('appointments.show', $appointment),
            'icon' => 'fa-regular fa-calendar-check',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'receptionist'], $payload, $request->user());
        $notifications->notifyUsers([$appointment->doctor], $payload);

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'department']);

        return view('modules.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'department']);

        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $doctors = User::query()->role('doctor')->orderBy('name')->get();
        $departments = Department::query()->orderBy('name')->get();
        $statusOptions = ['pending', 'approved', 'completed', 'cancelled'];

        return view('modules.appointments.edit', compact('appointment', 'patients', 'doctors', 'departments', 'statusOptions'));
    }

    public function update(AppointmentUpdateRequest $request, Appointment $appointment, HospitalNotificationService $notifications)
    {
        $appointment->update($request->validated());
        $appointment->load(['patient', 'doctor']);

        $patientName = trim((string) (($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '')));
        $payload = [
            'title' => 'Appointment updated',
            'message' => "{$patientName} appointment is now {$appointment->status}.",
            'module' => 'appointments',
            'type' => $appointment->status === 'cancelled' ? 'warning' : 'info',
            'url' => route('appointments.show', $appointment),
            'icon' => 'fa-solid fa-calendar-days',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'receptionist'], $payload, $request->user());
        $notifications->notifyUsers([$appointment->doctor], $payload);

        return redirect()
            ->route('appointments.show', $appointment)
            ->with('status', 'Appointment updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment deleted successfully.');
    }
}
