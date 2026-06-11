<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'pending'));
        $statusOptions = ['pending', 'approved', 'completed', 'cancelled'];

        $appointments = $this->visibleAppointments($request)
            ->with(['patient', 'doctor', 'department'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%");
                    })
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.appointments.index', compact('appointments', 'search', 'status', 'statusOptions'));
    }

    public function updateStatus(
        Request $request,
        Appointment $appointment,
        HospitalNotificationService $notifications
    ) {
        $this->ensureCanManageAppointment($request, $appointment);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'completed', 'cancelled'])],
        ]);

        $oldStatus = $appointment->status;
        $appointment->update(['status' => $validated['status']]);
        $appointment->load(['patient', 'doctor']);

        $patientName = trim((string) (($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '')));

        $payload = [
            'title' => 'Appointment status updated',
            'message' => "{$patientName} appointment changed from {$oldStatus} to {$appointment->status}.",
            'module' => 'appointments',
            'type' => $appointment->status === 'cancelled' ? 'warning' : 'info',
            'url' => route('admin.appointments.index', ['status' => $appointment->status]),
            'icon' => 'fa-solid fa-calendar-check',
        ];

        $notifications->notifyRoles(['super_admin', 'admin', 'receptionist'], $payload, $request->user());
        $notifications->notifyUsers([$appointment->doctor], $payload);

        return back()->with('status', 'Appointment status updated successfully.');
    }

    private function visibleAppointments(Request $request)
    {
        $query = Appointment::query();
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    private function ensureCanManageAppointment(Request $request, Appointment $appointment): void
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        abort_unless((int) $appointment->department_id === (int) $user->department_id, 403);
    }
}
