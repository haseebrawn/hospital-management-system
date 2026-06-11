<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PatientStoreRequest;
use App\Http\Requests\Web\PatientUpdateRequest;
use App\Models\Department;
use App\Models\Patient;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $patients = Patient::query()
            ->with('department')
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

        return view('modules.patients.show', compact('patient'));
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
}
