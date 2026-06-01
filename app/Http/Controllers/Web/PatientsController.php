<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PatientStoreRequest;
use App\Http\Requests\Web\PatientUpdateRequest;
use App\Models\Department;
use App\Models\Patient;
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
                    $q->where('first_name', 'like', "%{$search}%")
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

    public function store(PatientStoreRequest $request)
    {
        Patient::create($request->validated());

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

    public function update(PatientUpdateRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

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
