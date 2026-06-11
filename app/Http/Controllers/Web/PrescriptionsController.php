<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PrescriptionStoreRequest;
use App\Http\Requests\Web\PrescriptionUpdateRequest;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class PrescriptionsController extends Controller
{
    private array $statusOptions = ['pending', 'dispensed', 'cancelled'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $user = $request->user();

        $prescriptions = Prescription::query()
            ->with(['appointment', 'doctor', 'patient'])
            ->when($user->hasRole('doctor') && ! $user->hasAnyRole(['super_admin', 'admin']), fn ($query) => $query->where('doctor_id', $user->id))
            ->when($user->hasRole('admin') && ! $user->hasRole('super_admin'), function ($query) use ($user) {
                $query->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $user->department_id));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('medicines', 'like', "%{$search}%")
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

        $statusOptions = $this->statusOptions;

        return view('modules.prescriptions.index', compact('prescriptions', 'search', 'status', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $appointments = $this->appointmentOptions($request);
        $doctors = $this->doctorOptions($request);
        $statusOptions = $this->statusOptions;

        return view('modules.prescriptions.create', compact('appointments', 'doctors', 'statusOptions'));
    }

    public function store(PrescriptionStoreRequest $request, HospitalNotificationService $notifications)
    {
        $data = $this->payloadFromRequest($request);
        $prescription = Prescription::create($data);
        $prescription->load(['patient', 'doctor']);

        $this->notifyPrescription($request, $notifications, $prescription, 'Prescription created');

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('status', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        $this->authorizePrescriptionAccess(request(), $prescription);
        $prescription->load(['appointment', 'doctor', 'patient']);

        return view('modules.prescriptions.show', compact('prescription'));
    }

    public function edit(Request $request, Prescription $prescription)
    {
        $this->authorizePrescriptionAccess($request, $prescription);

        $appointments = $this->appointmentOptions($request);
        $doctors = $this->doctorOptions($request);
        $statusOptions = $this->statusOptions;

        return view('modules.prescriptions.edit', compact('prescription', 'appointments', 'doctors', 'statusOptions'));
    }

    public function update(PrescriptionUpdateRequest $request, Prescription $prescription, HospitalNotificationService $notifications)
    {
        $this->authorizePrescriptionAccess($request, $prescription);

        $prescription->update($this->payloadFromRequest($request));
        $prescription->load(['patient', 'doctor']);

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
}
