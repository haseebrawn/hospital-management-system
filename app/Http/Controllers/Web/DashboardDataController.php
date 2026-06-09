<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Billing;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardDataController extends Controller
{
    private function groupCountByMonth(string $table, string $dateColumn, int $months): array
    {
        $driver = DB::getDriverName();
        $start = now()->startOfMonth()->subMonths($months - 1)->startOfDay();
        $end = now()->endOfMonth()->endOfDay();

        $keyExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$dateColumn})",
            'pgsql' => "to_char({$dateColumn}, 'YYYY-MM')",
            default => "DATE_FORMAT({$dateColumn}, '%Y-%m')",
        };

        $rows = DB::table($table)
            ->selectRaw("{$keyExpr} as ym, count(*) as total")
            ->whereBetween($dateColumn, [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $labels[] = $ym;
            $data[] = (int) ($rows[$ym]->total ?? 0);
        }

        return compact('labels', 'data');
    }

    private function groupSumByMonth(string $table, string $dateColumn, string $sumColumn, int $months): array
    {
        $driver = DB::getDriverName();
        $start = now()->startOfMonth()->subMonths($months - 1)->startOfDay();
        $end = now()->endOfMonth()->endOfDay();

        $keyExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$dateColumn})",
            'pgsql' => "to_char({$dateColumn}, 'YYYY-MM')",
            default => "DATE_FORMAT({$dateColumn}, '%Y-%m')",
        };

        $rows = DB::table($table)
            ->selectRaw("{$keyExpr} as ym, sum({$sumColumn}) as total")
            ->whereBetween($dateColumn, [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $labels[] = $ym;
            $data[] = (float) ($rows[$ym]->total ?? 0);
        }

        return compact('labels', 'data');
    }

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

        $notifications = $request->user()
            ? $request->user()->notifications()
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'read_at' => optional($notification->read_at)->toIso8601String(),
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                    ...$notification->data,
                ])
                ->values()
                ->toArray()
            : [];

        $patientsTrend = $this->groupCountByMonth('patients', 'created_at', 6);
        $billingTrend = $this->groupSumByMonth('billings', 'created_at', 'total_amount', 6);
        $labTrend = $this->groupCountByMonth('lab_tests', 'created_at', 6);
        $medTrend = $this->groupCountByMonth('medicines', 'created_at', 6);

        $appointmentsByStatus = Appointment::query()
            ->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $appointmentStatusLabels = ['pending', 'approved', 'completed', 'cancelled'];
        $appointmentStatusData = array_map(
            fn ($s) => (int) ($appointmentsByStatus[$s]->total ?? 0),
            $appointmentStatusLabels
        );

        return response()->json([
            'total_patients' => $totalPatients,
            'new_appointments' => $newAppointments,
            'lab_tests_pending' => $labTestsPending,
            'todays_revenue' => $todaysRevenue,
            'available_beds' => $availableBeds,
            'occupied_beds' => $occupiedBeds,
            'recent_appointments' => $recentAppointments,
            'notifications' => $notifications,
            'charts' => [
                'patients_overview' => $patientsTrend,
                'revenue_overview' => [
                    'labels' => $billingTrend['labels'],
                    'billing' => $billingTrend['data'],
                    'lab_tests' => $labTrend['data'],
                    'medicines_added' => $medTrend['data'],
                ],
                'appointments_overview' => [
                    'labels' => $appointmentStatusLabels,
                    'data' => $appointmentStatusData,
                ],
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
