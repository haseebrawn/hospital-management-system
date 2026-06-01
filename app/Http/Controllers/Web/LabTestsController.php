<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LabTestStoreRequest;
use App\Http\Requests\Web\LabTestUpdateRequest;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
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

    public function create()
    {
        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $doctors = User::query()->role('doctor')->orderBy('name')->get();
        $technicians = User::query()->role('lab_technician')->orderBy('name')->get();
        $statusOptions = ['pending', 'in_process', 'completed'];

        return view('modules.lab-tests.create', compact('patients', 'doctors', 'technicians', 'statusOptions'));
    }

    public function store(LabTestStoreRequest $request)
    {
        LabTest::create($request->validated());

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

    public function update(LabTestUpdateRequest $request, LabTest $labTest)
    {
        $labTest->update($request->validated());

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
