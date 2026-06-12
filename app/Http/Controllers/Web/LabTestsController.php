<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LabTestStoreRequest;
use App\Http\Requests\Web\LabTestUpdateRequest;
use App\Models\Appointment;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class LabTestsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $tests = LabTest::query()
            ->with(['patient', 'doctor', 'technician'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('test_type', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($p) use ($search) {
                            $p->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('contact_number', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = ['pending', 'in_process', 'completed'];

        return view('modules.lab-tests.index', compact('tests', 'search', 'status', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $labTest = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::find($request->query('appointment_id'));

            if ($appointment) {
                $labTest = new LabTest([
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'results' => $appointment->reason,
                    'status' => 'pending',
                ]);
            }
        }

        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $doctors = User::query()->role('doctor')->orderBy('name')->get();
        $technicians = User::query()->role('lab_technician')->orderBy('name')->get();
        $statusOptions = ['pending', 'in_process', 'completed'];

        return view('modules.lab-tests.create', compact('labTest', 'patients', 'doctors', 'technicians', 'statusOptions'));
    }

    public function store(LabTestStoreRequest $request, HospitalNotificationService $notifications)
    {
        $labTest = LabTest::create($request->validated());
        $labTest->load(['patient', 'doctor', 'technician']);

        $patientName = trim((string) (($labTest->patient->first_name ?? '') . ' ' . ($labTest->patient->last_name ?? '')));
        $payload = [
            'title' => 'New lab test requested',
            'message' => "{$labTest->test_type} has been requested for {$patientName}.",
            'module' => 'lab-tests',
            'type' => 'success',
            'url' => route('lab-tests.show', $labTest),
            'icon' => 'fa-solid fa-flask',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'lab_technician'], $payload, $request->user());
        $notifications->notifyUsers([$labTest->doctor, $labTest->technician], $payload);

        return redirect()
            ->route('lab-tests.index')
            ->with('status', 'Lab test created successfully.');
    }

    public function show(LabTest $labTest)
    {
        $labTest->load(['patient', 'doctor', 'technician']);

        return view('modules.lab-tests.show', compact('labTest'));
    }

    public function edit(LabTest $labTest)
    {
        $labTest->load(['patient', 'doctor', 'technician']);

        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $doctors = User::query()->role('doctor')->orderBy('name')->get();
        $technicians = User::query()->role('lab_technician')->orderBy('name')->get();
        $statusOptions = ['pending', 'in_process', 'completed'];

        return view('modules.lab-tests.edit', compact('labTest', 'patients', 'doctors', 'technicians', 'statusOptions'));
    }

    public function update(LabTestUpdateRequest $request, LabTest $labTest, HospitalNotificationService $notifications)
    {
        $labTest->update($request->validated());
        $labTest->load(['patient', 'doctor', 'technician']);

        $patientName = trim((string) (($labTest->patient->first_name ?? '') . ' ' . ($labTest->patient->last_name ?? '')));
        $payload = [
            'title' => 'Lab test updated',
            'message' => "{$labTest->test_type} for {$patientName} is now {$labTest->status}.",
            'module' => 'lab-tests',
            'type' => $labTest->status === 'completed' ? 'success' : 'info',
            'url' => route('lab-tests.show', $labTest),
            'icon' => 'fa-solid fa-vial-circle-check',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'lab_technician'], $payload, $request->user());
        $notifications->notifyUsers([$labTest->doctor, $labTest->technician], $payload);

        return redirect()
            ->route('lab-tests.show', $labTest)
            ->with('status', 'Lab test updated successfully.');
    }

    public function destroy(LabTest $labTest)
    {
        $labTest->delete();

        return redirect()
            ->route('lab-tests.index')
            ->with('status', 'Lab test deleted successfully.');
    }
}
