<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DoctorAvailability;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorAvailabilitiesController extends Controller
{
    public function index(Request $request)
    {
        $doctorId = trim((string) $request->query('doctor_id', ''));
        $day = trim((string) $request->query('day_of_week', ''));
        $dayOptions = DoctorAvailability::dayOptions();

        $availabilities = DoctorAvailability::query()
            ->with('doctor.department')
            ->when(! $request->user()->hasAnyRole(['super_admin', 'admin']), function ($query) use ($request) {
                $query->where('doctor_id', $request->user()->id);
            })
            ->when($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin'), function ($query) use ($request) {
                $query->whereHas('doctor', fn ($doctorQuery) => $doctorQuery->where('department_id', $request->user()->department_id));
            })
            ->when($doctorId !== '', fn ($query) => $query->where('doctor_id', $doctorId))
            ->when($day !== '', fn ($query) => $query->where('day_of_week', $day))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        $doctors = $this->doctorOptions($request);

        return view('modules.doctor-availabilities.index', compact('availabilities', 'doctors', 'doctorId', 'day', 'dayOptions'));
    }

    public function create(Request $request)
    {
        $doctors = $this->doctorOptions($request);
        $dayOptions = DoctorAvailability::dayOptions();

        return view('modules.doctor-availabilities.create', compact('doctors', 'dayOptions'));
    }

    public function store(Request $request)
    {
        DoctorAvailability::create($this->validatedData($request));

        return redirect()
            ->route('doctor-availabilities.index')
            ->with('status', 'Doctor availability added successfully.');
    }

    public function edit(Request $request, DoctorAvailability $doctorAvailability)
    {
        $this->authorizeAvailabilityAccess($request, $doctorAvailability);

        $doctors = $this->doctorOptions($request);
        $dayOptions = DoctorAvailability::dayOptions();

        return view('modules.doctor-availabilities.edit', compact('doctorAvailability', 'doctors', 'dayOptions'));
    }

    public function update(Request $request, DoctorAvailability $doctorAvailability)
    {
        $this->authorizeAvailabilityAccess($request, $doctorAvailability);

        $doctorAvailability->update($this->validatedData($request));

        return redirect()
            ->route('doctor-availabilities.index')
            ->with('status', 'Doctor availability updated successfully.');
    }

    public function destroy(Request $request, DoctorAvailability $doctorAvailability)
    {
        $this->authorizeAvailabilityAccess($request, $doctorAvailability);

        $doctorAvailability->delete();

        return redirect()
            ->route('doctor-availabilities.index')
            ->with('status', 'Doctor availability deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $doctorId = $request->user()->hasRole('doctor')
            ? $request->user()->id
            : $request->input('doctor_id');

        $data = $request->validate([
            'doctor_id' => [
                $request->user()->hasRole('doctor') ? 'nullable' : 'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'day_of_week' => ['required', 'integer', Rule::in(array_keys(DoctorAvailability::dayOptions()))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $doctor = User::findOrFail($doctorId);
        abort_unless($doctor->hasRole('doctor'), 422, 'Selected user is not a doctor.');

        if ($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin')) {
            abort_unless((int) $doctor->department_id === (int) $request->user()->department_id, 403);
        }

        $data['doctor_id'] = $doctor->id;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function doctorOptions(Request $request)
    {
        return User::query()
            ->role('doctor', 'api')
            ->when($request->user()->hasRole('doctor'), fn ($query) => $query->whereKey($request->user()->id))
            ->when($request->user()->hasRole('admin') && ! $request->user()->hasRole('super_admin'), function ($query) use ($request) {
                $query->where('department_id', $request->user()->department_id);
            })
            ->with('department')
            ->orderBy('name')
            ->get();
    }

    private function authorizeAvailabilityAccess(Request $request, DoctorAvailability $availability): void
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('doctor')) {
            abort_unless((int) $availability->doctor_id === (int) $user->id, 403);
            return;
        }

        abort_unless($user->hasRole('admin'), 403);
        abort_unless((int) $availability->doctor?->department_id === (int) $user->department_id, 403);
    }
}
