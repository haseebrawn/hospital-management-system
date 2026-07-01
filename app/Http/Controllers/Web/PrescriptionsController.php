<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PrescriptionStoreRequest;
use App\Http\Requests\Web\PrescriptionUpdateRequest;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\User;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionsController extends Controller
{
    private array $statusOptions = ['pending', 'dispensed', 'cancelled'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $user = $request->user();

        $prescriptions = Prescription::query()
            ->with([
                'appointment' => function ($query) {
                    $query->with(['patient', 'doctor'])->withCount(['medicalRecords', 'prescriptions', 'labTests', 'billingItems']);
                },
                'doctor',
                'patient',
                'items',
            ])
            ->when($user->hasRole('doctor') && ! $user->hasAnyRole(['super_admin', 'admin']), fn ($query) => $query->where('doctor_id', $user->id))
            ->when($user->hasRole('admin') && ! $user->hasRole('super_admin'), function ($query) use ($user) {
                $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $user->department_id));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('medicines', 'like', "%{$search}%")
                        ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('medicine_name', 'like', "%{$search}%"))
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

        $prescriptions->getCollection()->transform(function (Prescription $prescription) {
            if ($prescription->appointment) {
                $prescription->appointment->workflowTimeline = $this->buildAppointmentWorkflowTimeline($prescription->appointment);
            }

            return $prescription;
        });

        $statusOptions = $this->statusOptions;

        return view('modules.prescriptions.index', compact('prescriptions', 'search', 'status', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $prescription = null;
        $linkedAppointment = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($request->query('appointment_id'));

            if ($appointment) {
                $linkedAppointment = $appointment;
                $prescription = new Prescription([
                    'appointment_id' => $appointment->id,
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'description' => trim((string) ($appointment->reason ?: $appointment->notes)),
                ]);
            }
        }

        $appointments = $this->appointmentOptions($request);
        $doctors = $this->doctorOptions($request);
        $medicines = $this->medicineOptions();
        $statusOptions = $this->statusOptions;

        return view('modules.prescriptions.create', compact('prescription', 'linkedAppointment', 'appointments', 'doctors', 'medicines', 'statusOptions'));
    }

    public function store(PrescriptionStoreRequest $request, HospitalNotificationService $notifications)
    {
        $prescription = DB::transaction(function () use ($request) {
            $prescription = Prescription::create($this->payloadFromRequest($request));
            $this->syncItems($prescription, $request);

            return $prescription;
        });

        $prescription->load(['patient', 'doctor', 'items']);

        $this->notifyPrescription($request, $notifications, $prescription, 'Prescription created');

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('status', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        $this->authorizePrescriptionAccess(request(), $prescription);
        $prescription->load(['appointment', 'doctor', 'patient', 'items.medicine']);

        return view('modules.prescriptions.show', compact('prescription'));
    }

    public function edit(Request $request, Prescription $prescription)
    {
        $this->authorizePrescriptionAccess($request, $prescription);

        $appointments = $this->appointmentOptions($request);
        $doctors = $this->doctorOptions($request);
        $medicines = $this->medicineOptions();
        $statusOptions = $this->statusOptions;

        $prescription->load(['items', 'appointment.patient', 'appointment.doctor']);
        $linkedAppointment = $prescription->appointment;

        return view('modules.prescriptions.edit', compact('prescription', 'linkedAppointment', 'appointments', 'doctors', 'medicines', 'statusOptions'));
    }

    public function update(PrescriptionUpdateRequest $request, Prescription $prescription, HospitalNotificationService $notifications)
    {
        $this->authorizePrescriptionAccess($request, $prescription);

        DB::transaction(function () use ($request, $prescription) {
            $prescription->update($this->payloadFromRequest($request));
            $this->syncItems($prescription, $request);
        });

        $prescription->load(['patient', 'doctor', 'items']);

        $this->notifyPrescription($request, $notifications, $prescription, 'Prescription updated');

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('status', 'Prescription updated successfully.');
    }

    public function destroy(Request $request, Prescription $prescription)
    {
        $this->authorizePrescriptionAccess($request, $prescription);
        $prescription->delete();

        return redirect()
            ->route('prescriptions.index')
            ->with('status', 'Prescription deleted successfully.');
    }

    private function payloadFromRequest(Request $request): array
    {
        $appointment = Appointment::with('patient')->findOrFail($request->input('appointment_id'));
        $doctorId = $request->user()->hasRole('doctor')
            ? $request->user()->id
            : ($request->input('doctor_id') ?: $appointment->doctor_id);

        abort_unless($doctorId, 422, 'Doctor is required for prescription.');

        if ($request->user()->hasRole('doctor')) {
            abort_unless((int) $appointment->doctor_id === (int) $request->user()->id || ! $appointment->doctor_id, 403);
        }

        if ($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin')) {
            abort_unless((int) $appointment->patient?->department_id === (int) $request->user()->department_id, 403);
        }

        return [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctorId,
            'patient_id' => $appointment->patient_id,
            'description' => $request->input('description'),
            'medicines' => $request->input('medicines'),
            'status' => $request->input('status', 'pending'),
        ];
    }

    private function appointmentOptions(Request $request)
    {
        $user = $request->user();

        return Appointment::query()
            ->with(['patient', 'doctor'])
            ->when($user->hasRole('doctor') && ! $user->hasAnyRole(['super_admin', 'admin']), fn ($query) => $query->where('doctor_id', $user->id))
            ->when($user->hasRole('admin') && ! $user->hasRole('super_admin'), function ($query) use ($user) {
                $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $user->department_id));
            })
            ->orderByDesc('date')
            ->orderByDesc('time')
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

    private function medicineOptions()
    {
        return Medicine::query()
            ->orderBy('name')
            ->get(['id', 'name', 'stock', 'status']);
    }

    private function syncItems(Prescription $prescription, Request $request): void
    {
        $items = collect($request->input('items', []))
            ->map(function (array $item) {
                $medicine = ! empty($item['medicine_id']) ? Medicine::find($item['medicine_id']) : null;
                $medicineName = trim((string) ($item['medicine_name'] ?? ''));

                if ($medicine && $medicineName === '') {
                    $medicineName = $medicine->name;
                }

                return [
                    'medicine_id' => $medicine?->id,
                    'medicine_name' => $medicineName,
                    'dosage' => filled($item['dosage'] ?? null) ? $item['dosage'] : null,
                    'frequency' => filled($item['frequency'] ?? null) ? $item['frequency'] : null,
                    'duration' => filled($item['duration'] ?? null) ? $item['duration'] : null,
                    'quantity' => filled($item['quantity'] ?? null) ? (int) $item['quantity'] : null,
                    'instructions' => filled($item['instructions'] ?? null) ? $item['instructions'] : null,
                ];
            })
            ->filter(fn (array $item) => $item['medicine_name'] !== '')
            ->values();

        $prescription->items()->delete();

        if ($items->isNotEmpty()) {
            $prescription->items()->createMany($items->all());
        }
    }

    private function authorizePrescriptionAccess(Request $request, Prescription $prescription): void
    {
        $prescription->loadMissing('patient');
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('doctor')) {
            abort_unless((int) $prescription->doctor_id === (int) $user->id, 403);
            return;
        }

        if ($user->hasRole('admin')) {
            abort_unless((int) $prescription->patient?->department_id === (int) $user->department_id, 403);
            return;
        }

        abort(403);
    }

    private function notifyPrescription(
        Request $request,
        HospitalNotificationService $notifications,
        Prescription $prescription,
        string $title
    ): void {
        $patientName = trim((string) (($prescription->patient->first_name ?? '') . ' ' . ($prescription->patient->last_name ?? '')));

        $payload = [
            'title' => $title,
            'message' => "{$patientName} has a {$prescription->status} prescription.",
            'module' => 'prescriptions',
            'type' => 'info',
            'url' => route('prescriptions.show', $prescription),
            'icon' => 'fa-solid fa-prescription-bottle-medical',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'pharmacist'], $payload, $request->user());
        $notifications->notifyUsers([$prescription->doctor], $payload);
    }

    private function buildAppointmentWorkflowTimeline(Appointment $appointment): array
    {
        $medicalRecordsCount = (int) ($appointment->medical_records_count ?? 0);
        $prescriptionsCount = (int) ($appointment->prescriptions_count ?? 0);
        $labTestsCount = (int) ($appointment->lab_tests_count ?? 0);
        $billingItemsCount = (int) ($appointment->billing_items_count ?? 0);

        return [
            [
                'label' => 'Check In',
                'done' => (bool) $appointment->checked_in_at,
            ],
            [
                'label' => 'Medical Record',
                'done' => $medicalRecordsCount > 0,
            ],
            [
                'label' => 'Prescription',
                'done' => $prescriptionsCount > 0,
            ],
            [
                'label' => 'Lab Test',
                'done' => $labTestsCount > 0,
            ],
            [
                'label' => 'Billing',
                'done' => $billingItemsCount > 0,
            ],
        ];
    }
}
