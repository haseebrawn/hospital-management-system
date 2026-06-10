<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BillingItem;
use App\Models\Medicine;
use App\Services\DashboardScopeService;
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

    private function groupQueryCountByMonth($query, int $months): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1)->startOfDay();
        $end = now()->endOfMonth()->endOfDay();

        $rows = $query->whereBetween('created_at', [$start, $end])
            ->get(['created_at'])
            ->groupBy(fn ($row) => optional($row->created_at)->format('Y-m'));

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $labels[] = $ym;
            $data[] = (int) ($rows[$ym]?->count() ?? 0);
        }

        return compact('labels', 'data');
    }

    private function groupQuerySumByMonth($query, string $sumColumn, int $months): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1)->startOfDay();
        $end = now()->endOfMonth()->endOfDay();

        $rows = $query->whereBetween('created_at', [$start, $end])
            ->get(['created_at', $sumColumn])
            ->groupBy(fn ($row) => optional($row->created_at)->format('Y-m'));

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $labels[] = $ym;
            $data[] = (float) ($rows[$ym]?->sum($sumColumn) ?? 0);
        }

        return compact('labels', 'data');
    }

    public function __invoke(Request $request, DashboardScopeService $dashboardScope)
    {
        $today = now()->toDateString();
        $user = $request->user();
        $visibility = $dashboardScope->visibility($user);

        $totalPatients = $visibility['patients'] ? $dashboardScope->patients($user)->count() : 0;
        $newAppointments = $visibility['appointments'] ? $dashboardScope->appointments($user)->whereDate('created_at', $today)->count() : 0;
        $todaysAppointments = $visibility['appointments'] ? $dashboardScope->appointments($user)->whereDate('date', $today)->count() : 0;
        $pendingAppointments = $visibility['appointments'] ? $dashboardScope->appointments($user)->where('status', 'pending')->count() : 0;
        $approvedAppointments = $visibility['appointments'] ? $dashboardScope->appointments($user)->where('status', 'approved')->count() : 0;
        $labTestsPending = $visibility['lab_tests'] ? $dashboardScope->labTests($user)->where('status', 'pending')->count() : 0;
        $todaysRevenue = $visibility['revenue'] ? (float) $dashboardScope->billings($user)->whereDate('created_at', $today)->sum('total_amount') : 0;
        $pendingInvoices = $visibility['revenue'] ? $dashboardScope->billings($user)->where('status', 'pending')->count() : 0;
        $lowStockMedicines = $visibility['pharmacy'] ? Medicine::query()->where('stock', '<=', 10)->count() : 0;
        $medicineSalesQuery = BillingItem::query()
            ->where('type', 'medicine')
            ->whereHas('billing', fn ($billingQuery) => $billingQuery->where('status', 'paid'));
        $medicineSoldQuantity = $visibility['pharmacy'] ? (clone $medicineSalesQuery)->sum('quantity') : 0;
        $medicineSalesAmount = $visibility['pharmacy']
            ? ((clone $medicineSalesQuery)->selectRaw('sum(quantity * price) as total')->value('total') ?? 0)
            : 0;
        $activeStaff = $visibility['staff'] ? $dashboardScope->staff($user)->where('employment_status', 'active')->count() : 0;

        $recentAppointments = $visibility['appointments']
            ? $dashboardScope->appointments($user)
                ->with(['patient', 'doctor'])
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->limit(5)
                ->get()
                ->map(fn (Appointment $appointment) => [
                    'id' => $appointment->id,
                    'patient' => trim((string) (optional($appointment->patient)->first_name . ' ' . optional($appointment->patient)->last_name)) ?: '—',
                    'doctor' => optional($appointment->doctor)->name ?? '—',
                    'status' => (string) ($appointment->status ?? 'pending'),
                ])
            : collect();

        $availableBeds = $visibility['beds'] ? $dashboardScope->beds($user)->where('status', 'available')->count() : 0;
        $occupiedBeds = $visibility['beds'] ? $dashboardScope->beds($user)->where('status', 'occupied')->count() : 0;

        $notifications = $user
            ? $user->notifications()
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

        $patientsTrend = $visibility['patients']
            ? $this->groupQueryCountByMonth($dashboardScope->patients($user), 6)
            : ['labels' => [], 'data' => []];
        $billingTrend = $visibility['revenue']
            ? $this->groupQuerySumByMonth($dashboardScope->billings($user), 'total_amount', 6)
            : ['labels' => [], 'data' => []];
        $labTrend = $visibility['lab_tests']
            ? $this->groupQueryCountByMonth($dashboardScope->labTests($user), 6)
            : ['labels' => [], 'data' => []];
        $medTrend = $visibility['pharmacy']
            ? $this->groupCountByMonth('medicines', 'created_at', 6)
            : ['labels' => $billingTrend['labels'], 'data' => []];

        $appointmentsByStatus = $visibility['appointments']
            ? $dashboardScope->appointments($user)
                ->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->keyBy('status')
            : collect();

        $appointmentStatusLabels = ['pending', 'approved', 'completed', 'cancelled'];
        $appointmentStatusData = array_map(
            fn ($status) => (int) ($appointmentsByStatus[$status]->total ?? 0),
            $appointmentStatusLabels
        );
        $activityLabels = $visibility['revenue']
            ? $billingTrend['labels']
            : ($visibility['lab_tests'] ? $labTrend['labels'] : $medTrend['labels']);

        return response()->json([
            'total_patients' => $totalPatients,
            'new_appointments' => $newAppointments,
            'todays_appointments' => $todaysAppointments,
            'pending_appointments' => $pendingAppointments,
            'approved_appointments' => $approvedAppointments,
            'lab_tests_pending' => $labTestsPending,
            'todays_revenue' => $todaysRevenue,
            'pending_invoices' => $pendingInvoices,
            'low_stock_medicines' => $lowStockMedicines,
            'medicine_sold_quantity' => $medicineSoldQuantity,
            'medicine_sales_amount' => $medicineSalesAmount,
            'active_staff' => $activeStaff,
            'available_beds' => $availableBeds,
            'occupied_beds' => $occupiedBeds,
            'recent_appointments' => $recentAppointments,
            'notifications' => $notifications,
            'visibility' => $visibility,
            'charts' => [
                'patients_overview' => $patientsTrend,
                'revenue_overview' => [
                    'labels' => $activityLabels,
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
