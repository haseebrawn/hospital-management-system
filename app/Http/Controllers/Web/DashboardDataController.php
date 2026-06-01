<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Billing;
use App\Models\LabTest;
use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardDataController extends Controller
{
    public function __invoke(Request $request)
    {
        $today = now()->toDateString();

        $totalPatients = Patient::count();
        $newAppointments = Appointment::whereDate('created_at', $today)->count();
        $labTestsPending = LabTest::where('status', 'pending')->count();
        $todaysRevenue = (float) Billing::whereDate('created_at', $today)->sum('total_amount');

        $recentAppointments = Appointment::query()
            ->with(['patient', 'doctor'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(5)
            ->get()
            ->map(function (Appointment $a) {
                return [
                    'id' => $a->id,
                    'patient' => trim((string) (optional($a->patient)->first_name . ' ' . optional($a->patient)->last_name)) ?: '—',
                    'doctor' => optional($a->doctor)->name ?? '—',
                    'status' => (string) ($a->status ?? 'pending'),
                ];
            });

        $availableBeds = Bed::where('status', 'available')->count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();

        $user = $request->user();
        $notifications = [];
        if ($user) {
            $notifications = $user->notifications()
                ->latest()
                ->limit(6)
                ->get()
                ->map(function ($n) {
                    $title = $n->data['title'] ?? null;
                    $message = $n->data['message'] ?? null;
                    $text = $message ?: $title ?: $n->type;

                    return [
                        'id' => $n->id,
                        'text' => (string) $text,
                        'created_at' => optional($n->created_at)->toIso8601String(),
                        'read_at' => optional($n->read_at)->toIso8601String(),
                    ];
                })
                ->toArray();
        }

        return response()->json([
            'total_patients' => $totalPatients,
            'new_appointments' => $newAppointments,
            'lab_tests_pending' => $labTestsPending,
            'todays_revenue' => $todaysRevenue,
            'available_beds' => $availableBeds,
            'occupied_beds' => $occupiedBeds,
            'recent_appointments' => $recentAppointments,
            'notifications' => $notifications,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}

