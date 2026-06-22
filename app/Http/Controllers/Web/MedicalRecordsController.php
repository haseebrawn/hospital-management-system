<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MedicalRecordStoreRequest;
use App\Http\Requests\Web\MedicalRecordUpdateRequest;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class MedicalRecordsController extends Controller
{
    private array $visitTypes = ['consultation', 'follow_up', 'emergency', 'admission', 'discharge'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $visitType = trim((string) $request->query('visit_type', ''));
        $user = $request->user();

        $records = MedicalRecord::query()
            ->with(['patient', 'doctor', 'appointment'])
            ->when($user->hasRole('doctor') && ! $user->hasAnyRole(['super_admin', 'admin']), fn ($query) => $query->where('doctor_id', $user->id))
            ->when($user->hasRole('admin') && ! $user->hasRole('super_admin'), function ($query) use ($user) {
                $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $user->department_id));
            })
            ->when($visitType !== '', fn ($query) => $query->where('visit_type', $visitType))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('chief_complaint', 'like', "%{$search}%")
                        ->orWhere('diagnosis', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($patientQuery) use ($search) {
                            $patientQuery->where('mrn', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('contact_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $visitTypes = $this->visitTypes;

        return view('modules.medical-records.index', compact('records', 'search', 'visitType', 'visitTypes'));
    }

    public function create(Request $request)
    {
        $medicalRecord = null;
        $linkedAppointment = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($request->query('appointment_id'));

            if ($appointment) {
                $linkedAppointment = $appointment;
                $medicalRecord = new MedicalRecord([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'chief_complaint' => $appointment->reason,
                    'notes' => $appointment->notes,
                ]);
            }
        }

        $patients = $this->patientOptions($request);
        $doctors = $this->doctorOptions($request);
        $appointments = $this->appointmentOptions($request);
        $visitTypes = $this->visitTypes;

        return view('modules.medical-records.create', compact('medicalRecord', 'linkedAppointment', 'patients', 'doctors', 'appointments', 'visitTypes'));
    }

    public function store(MedicalRecordStoreRequest $request, HospitalNotificationService $notifications)
    {
        $data = $this->payloadFromRequest($request);
        $record = MedicalRecord::create($data);
        $record->load(['patient', 'doctor']);

        $this->notifyMedicalRecord($request, $notifications, $record, 'Medical record created');

        return redirect()
            ->route('medical-records.show', $record)
            ->with('status', 'Medical record created successfully.');
    }

    public function show(Request $request, MedicalRecord $medicalRecord)
    {
        $this->authorizeMedicalRecordAccess($request, $medicalRecord);
        $medicalRecord->load(['patient', 'doctor', 'appointment']);

        return view('modules.medical-records.show', compact('medicalRecord'));
    }

    public function edit(Request $request, MedicalRecord $medicalRecord)
    {
        $this->authorizeMedicalRecordAccess($request, $medicalRecord);

        $patients = $this->patientOptions($request);
        $doctors = $this->doctorOptions($request);
        $appointments = $this->appointmentOptions($request);
        $visitTypes = $this->visitTypes;
        $medicalRecord->loadMissing('appointment.patient', 'appointment.doctor');
        $linkedAppointment = $medicalRecord->appointment;

        return view('modules.medical-records.edit', compact('medicalRecord', 'linkedAppointment', 'patients', 'doctors', 'appointments', 'visitTypes'));
    }

    public function update(MedicalRecordUpdateRequest $request, MedicalRecord $medicalRecord, HospitalNotificationService $notifications)
    {
        $this->authorizeMedicalRecordAccess($request, $medicalRecord);

        $medicalRecord->update($this->payloadFromRequest($request));
        $medicalRecord->load(['patient', 'doctor']);

        $this->notifyMedicalRecord($request, $notifications, $medicalRecord, 'Medical record updated');

        return redirect()
            ->route('medical-records.show', $medicalRecord)
            ->with('status', 'Medical record updated successfully.');
    }

    public function destroy(Request $request, MedicalRecord $medicalRecord)
    {
        $this->authorizeMedicalRecordAccess($request, $medicalRecord);
        $medicalRecord->delete();

        return redirect()
            ->route('medical-records.index')
            ->with('status', 'Medical record deleted successfully.');
    }

    private function payloadFromRequest(Request $request): array
    {
        $patient = Patient::findOrFail($request->input('patient_id'));
        $appointment = $request->filled('appointment_id')
            ? Appointment::with('patient')->findOrFail($request->input('appointment_id'))
            : null;
        $doctorId = $request->user()->hasRole('doctor')
            ? $request->user()->id
            : ($request->input('doctor_id') ?: $appointment?->doctor_id);

        if ($appointment) {
            abort_unless((int) $appointment->patient_id === (int) $patient->id, 422, 'Appointment does not belong to selected patient.');
        }

        if ($request->user()->hasRole('doctor')) {
            abort_unless(! $appointment || (int) $appointment->doctor_id === (int) $request->user()->id || ! $appointment->doctor_id, 403);
        }

        if ($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin')) {
            abort_unless((int) $patient->department_id === (int) $request->user()->department_id, 403);
        }

        return [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorId,
            'appointment_id' => $appointment?->id,
            'visit_type' => $request->input('visit_type', 'consultation'),
            'chief_complaint' => $request->input('chief_complaint'),
            'diagnosis' => $request->input('diagnosis'),
            'vitals' => $request->input('vitals'),
            'history' => $request->input('history'),
            'allergies' => $request->input('allergies'),
            'notes' => $request->input('notes'),
            'follow_up_date' => $request->input('follow_up_date'),
        ];
    }

    private function patientOptions(Request $request)
    {
        return Patient::query()
            ->when($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin'), function ($query) use ($request) {
                $query->where('department_id', $request->user()->department_id);
            })
            ->orderByDesc('id')
            ->limit(300)
            ->get();
    }

    private function doctorOptions(Request $request)
    {
        return User::query()
            ->role('doctor', 'api')
            ->when($request->user()->hasRole('doctor'), fn ($query) => $query->whereKey($request->user()->id))
            ->when($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin'), function ($query) use ($request) {
                $query->where('department_id', $request->user()->department_id);
            })
            ->orderBy('name')
            ->get();
    }

    private function appointmentOptions(Request $request)
    {
        return Appointment::query()
            ->with(['patient', 'doctor'])
            ->when($request->user()->hasRole('doctor') && ! $request->user()->hasAnyRole(['super_admin', 'admin']), fn ($query) => $query->where('doctor_id', $request->user()->id))
            ->when($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin'), function ($query) use ($request) {
                $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $request->user()->department_id));
            })
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(300)
            ->get();
    }

    private function authorizeMedicalRecordAccess(Request $request, MedicalRecord $medicalRecord): void
    {
        $medicalRecord->loadMissing('patient');
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('doctor')) {
            abort_unless((int) $medicalRecord->doctor_id === (int) $user->id, 403);
            return;
        }

        if ($user->hasRole('admin')) {
            abort_unless((int) $medicalRecord->patient?->department_id === (int) $user->department_id, 403);
            return;
        }

        abort(403);
    }

    private function notifyMedicalRecord(Request $request, HospitalNotificationService $notifications, MedicalRecord $record, string $title): void
    {
        $patientName = trim((string) (($record->patient->first_name ?? '') . ' ' . ($record->patient->last_name ?? '')));
        $payload = [
            'title' => $title,
            'message' => "{$patientName} has a {$record->visit_type} medical record.",
            'module' => 'medical-records',
            'type' => 'info',
            'url' => route('medical-records.show', $record),
            'icon' => 'fa-solid fa-notes-medical',
        ];

        $notifications->notifyRoles(['super_admin', 'admin'], $payload, $request->user());
        $notifications->notifyUsers([$record->doctor], $payload);
    }
}
