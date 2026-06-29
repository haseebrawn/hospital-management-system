<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AppointmentStoreRequest;
use App\Http\Requests\Web\AppointmentUpdateRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\BillingItem;
use App\Models\LabTest;
use App\Models\Prescription;
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
            ->withCount(['medicalRecords', 'prescriptions', 'labTests', 'billingItems'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('patient', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%");
                    })
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(15)
            ->withQueryString();

        $appointments->getCollection()->transform(function (Appointment $appointment) {
            $appointment->workflowTimeline = $this->buildWorkflowTimeline($appointment);

            return $appointment;
        });

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
        $workflowTimeline = $this->buildWorkflowTimeline($appointment);

        return view('modules.appointments.show', compact('appointment', 'workflowTimeline'));
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

    public function checkIn(Request $request, Appointment $appointment, HospitalNotificationService $notifications)
    {
        abort_unless($appointment->canCheckIn(), 422, 'Appointment cannot be checked in.');

        $appointment->forceFill([
            'checked_in_at' => now(),
        ])->save();

        $this->notifyVisitWorkflow($request, $notifications, $appointment, 'Patient checked in');

        return back()->with('status', 'Patient checked in successfully.');
    }

    public function checkOut(Request $request, Appointment $appointment, HospitalNotificationService $notifications)
    {
        abort_unless($appointment->canCheckOut(), 422, 'Appointment cannot be checked out.');

        $appointment->forceFill([
            'checked_out_at' => now(),
            'status' => $appointment->status === 'approved' ? 'completed' : $appointment->status,
        ])->save();

        $this->notifyVisitWorkflow($request, $notifications, $appointment, 'Patient checked out');

        return back()->with('status', 'Patient checked out successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment deleted successfully.');
    }

    private function notifyVisitWorkflow(
        Request $request,
        HospitalNotificationService $notifications,
        Appointment $appointment,
        string $title
    ): void {
        $appointment->load(['patient', 'doctor']);
        $patientName = trim((string) (($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '')));

        $payload = [
            'title' => $title,
            'message' => "{$patientName} visit status is now " . str_replace('_', ' ', $appointment->visit_status) . '.',
            'module' => 'appointments',
            'type' => 'info',
            'url' => route('appointments.show', $appointment),
            'icon' => 'fa-solid fa-clipboard-check',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'receptionist', 'nurse'], $payload, $request->user());
        $notifications->notifyUsers([$appointment->doctor], $payload);
    }

    private function buildWorkflowTimeline(Appointment $appointment): array
    {
        $appointmentId = $appointment->id;
        $patientName = trim((string) (($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '')));

        $hasMedicalRecord = $appointment->medicalRecords()->exists();
        $hasPrescription = Prescription::query()->where('appointment_id', $appointmentId)->exists();
        $hasLabTest = LabTest::query()->where('appointment_id', $appointmentId)->exists();
        $hasBilling = BillingItem::query()
            ->where('source_type', 'appointment')
            ->where('source_id', $appointmentId)
            ->exists();

        return [
            [
                'key' => 'check_in',
                'label' => 'Check In',
                'done' => (bool) $appointment->checked_in_at,
                'subtitle' => $appointment->checked_in_at
                    ? 'Checked in at ' . optional($appointment->checked_in_at)->format('Y-m-d H:i')
                    : 'Waiting to be checked in',
            ],
            [
                'key' => 'medical_record',
                'label' => 'Medical Record',
                'done' => $hasMedicalRecord,
                'subtitle' => $hasMedicalRecord
                    ? "Clinical note prepared for {$patientName}"
                    : 'No medical record yet',
            ],
            [
                'key' => 'prescription',
                'label' => 'Prescription',
                'done' => $hasPrescription,
                'subtitle' => $hasPrescription
                    ? 'Prescription created for this visit'
                    : 'No prescription yet',
            ],
            [
                'key' => 'lab',
                'label' => 'Lab Test',
                'done' => $hasLabTest,
                'subtitle' => $hasLabTest
                    ? 'Lab request linked to this appointment'
                    : 'No lab test request yet',
            ],
            [
                'key' => 'billing',
                'label' => 'Billing',
                'done' => $hasBilling,
                'subtitle' => $hasBilling
                    ? 'Invoice created from appointment services'
                    : 'No billing record linked yet',
            ],
        ];
    }
}
