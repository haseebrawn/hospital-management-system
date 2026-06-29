<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PatientStoreRequest;
use App\Http\Requests\Web\PatientUpdateRequest;
use App\Models\Appointment;
use App\Models\BillingItem;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Prescription;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $patients = Patient::query()
            ->with(['department', 'latestAppointment.doctor'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('mrn', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $patients->getCollection()->transform(function (Patient $patient) {
            if ($patient->latestAppointment) {
                $patient->latestAppointment->workflowTimeline = $this->buildWorkflowTimeline($patient->latestAppointment);
            }

            return $patient;
        });

        return view('modules.patients.index', compact('patients', 'search'));
    }

    public function create()
    {
        $departments = Department::query()->orderBy('name')->get();

        return view('modules.patients.create', compact('departments'));
    }

    public function store(PatientStoreRequest $request, HospitalNotificationService $notifications)
    {
        $patient = Patient::create($request->validated());

        $notifications->notifyRoles(['super_admin', 'admin', 'doctor', 'nurse', 'receptionist'], [
            'title' => 'New patient registered',
            'message' => trim("{$patient->first_name} {$patient->last_name}") . ' has been added to the patient registry.',
            'module' => 'patients',
            'type' => 'success',
            'url' => route('patients.show', $patient),
            'icon' => 'fa-solid fa-user-plus',
        ], $request->user());

        return redirect()
            ->route('patients.index')
            ->with('status', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $patient->load('department');

        $latestAppointment = Appointment::query()
            ->with('doctor')
            ->withCount(['medicalRecords', 'prescriptions', 'labTests', 'billingItems'])
            ->where('patient_id', $patient->id)
            ->latest('date')
            ->latest('time')
            ->first();

        if ($latestAppointment) {
            $latestAppointment->workflowTimeline = $this->buildWorkflowTimeline($latestAppointment);
        }

        return view('modules.patients.show', compact('patient', 'latestAppointment'));
    }

    public function history(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($request, $patient);

        $patient->load('department');

        $medicalRecords = $patient->medicalRecords()
            ->with(['doctor', 'appointment'])
            ->latest()
            ->limit(25)
            ->get();

        $prescriptions = Prescription::query()
            ->with(['doctor', 'appointment', 'items.medicine'])
            ->where('patient_id', $patient->id)
            ->latest()
            ->limit(25)
            ->get();

        $appointments = Appointment::query()
            ->with('doctor')
            ->withCount(['medicalRecords', 'prescriptions', 'labTests', 'billingItems'])
            ->where('patient_id', $patient->id)
            ->latest('date')
            ->latest('time')
            ->limit(25)
            ->get();

        $appointments->transform(function (Appointment $appointment) {
            $appointment->workflowTimeline = $this->buildWorkflowTimeline($appointment);

            return $appointment;
        });

        return view('modules.patients.history', compact('patient', 'medicalRecords', 'prescriptions', 'appointments'));
    }

    public function edit(Patient $patient)
    {
        $departments = Department::query()->orderBy('name')->get();

        return view('modules.patients.edit', compact('patient', 'departments'));
    }

    public function update(PatientUpdateRequest $request, Patient $patient, HospitalNotificationService $notifications)
    {
        $patient->update($request->validated());

        $notifications->notifyRoles(['super_admin', 'admin', 'doctor', 'nurse', 'receptionist'], [
            'title' => 'Patient profile updated',
            'message' => trim("{$patient->first_name} {$patient->last_name}") . ' profile information was updated.',
            'module' => 'patients',
            'type' => 'info',
            'url' => route('patients.show', $patient),
            'icon' => 'fa-solid fa-user-pen',
        ], $request->user());

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('status', 'Patient deleted successfully.');
    }

    private function authorizePatientAccess(Request $request, Patient $patient): void
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasAnyRole(['doctor', 'nurse', 'receptionist'])) {
            abort_unless((int) $patient->department_id === (int) $user->department_id, 403);
            return;
        }

        abort(403);
    }

    private function buildWorkflowTimeline(Appointment $appointment): array
    {
        $medicalRecordsCount = (int) ($appointment->medical_records_count ?? 0);
        $prescriptionsCount = (int) ($appointment->prescriptions_count ?? 0);
        $labTestsCount = (int) ($appointment->lab_tests_count ?? 0);
        $billingItemsCount = (int) ($appointment->billing_items_count ?? 0);

        $steps = [
            [
                'key' => 'check_in',
                'label' => 'Check In',
                'done' => (bool) $appointment->checked_in_at,
                'subtitle' => $appointment->checked_in_at ? 'Patient checked in' : 'Waiting for check-in',
            ],
            [
                'key' => 'medical_record',
                'label' => 'Medical Record',
                'done' => $medicalRecordsCount > 0,
                'subtitle' => $medicalRecordsCount > 0 ? 'Clinical notes saved' : 'No medical record yet',
            ],
            [
                'key' => 'prescription',
                'label' => 'Prescription',
                'done' => $prescriptionsCount > 0,
                'subtitle' => $prescriptionsCount > 0 ? 'Prescription issued' : 'Pending prescription',
            ],
            [
                'key' => 'lab_test',
                'label' => 'Lab Test',
                'done' => $labTestsCount > 0,
                'subtitle' => $labTestsCount > 0 ? 'Lab request or result linked' : 'No lab activity yet',
            ],
            [
                'key' => 'billing',
                'label' => 'Billing',
                'done' => $billingItemsCount > 0,
                'subtitle' => $billingItemsCount > 0 ? 'Billing linked to visit' : 'No billing entry yet',
            ],
        ];

        return $steps;
    }
}
