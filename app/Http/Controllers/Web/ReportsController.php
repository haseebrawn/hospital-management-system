<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Ward;
use App\Services\DashboardScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request, DashboardScopeService $dashboardScope)
    {
        $reportVisibility = $dashboardScope->visibility($request->user());

        return view('modules.reports.index', compact('reportVisibility'));
    }

    public function patients(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'patients');

        $from = $request->query('from');
        $to = $request->query('to');

        $q = $dashboardScope->patients($request->user());
        if ($from) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        $total = (clone $q)->count();
        $byGender = (clone $q)->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->orderBy('gender')
            ->get();
        $recent = (clone $q)->orderByDesc('id')->limit(50)->get();

        return view('modules.reports.patients', compact('from', 'to', 'total', 'byGender', 'recent'));
    }

    public function appointments(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'appointments');

        $from = $request->query('from');
        $to = $request->query('to');

        $q = $dashboardScope->appointments($request->user());
        if ($from) {
            $q->whereDate('date', '>=', $from);
        }
        if ($to) {
            $q->whereDate('date', '<=', $to);
        }

        $total = (clone $q)->count();
        $byStatus = (clone $q)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $recent = (clone $q)->with(['patient', 'doctor'])->orderByDesc('date')->orderByDesc('time')->limit(50)->get();

        return view('modules.reports.appointments', compact('from', 'to', 'total', 'byStatus', 'recent'));
    }

    public function billing(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'revenue');

        $from = $request->query('from');
        $to = $request->query('to');

        $q = $dashboardScope->billings($request->user());
        if ($from) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        $total = (clone $q)->count();
        $sumTotal = (clone $q)->sum('total_amount');
        $byStatus = (clone $q)->select('status', DB::raw('count(*) as total'), DB::raw('sum(total_amount) as amount'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $recent = (clone $q)->with('patient')->orderByDesc('id')->limit(50)->get();

        return view('modules.reports.billing', compact('from', 'to', 'total', 'sumTotal', 'byStatus', 'recent'));
    }

    public function labTests(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'lab_tests');

        $from = $request->query('from');
        $to = $request->query('to');

        $q = $dashboardScope->labTests($request->user());
        if ($from) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        $total = (clone $q)->count();
        $byStatus = (clone $q)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $recent = (clone $q)->with(['patient', 'doctor', 'technician'])->orderByDesc('id')->limit(50)->get();

        return view('modules.reports.lab-tests', compact('from', 'to', 'total', 'byStatus', 'recent'));
    }

    public function pharmacy(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'pharmacy');

        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');

        $q = Medicine::query();
        if ($status) {
            $q->where('status', $status);
        }

        $total = (clone $q)->count();
        $lowStock = (clone $q)->where('stock', '<=', 10)->count();
        $byStatus = (clone $q)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $recent = (clone $q)->orderByDesc('id')->limit(50)->get();

        $medicineSalesQuery = BillingItem::query()
            ->where('type', 'medicine')
            ->whereHas('billing', function ($billingQuery) use ($from, $to) {
                $billingQuery->where('status', 'paid');

                if ($from) {
                    $billingQuery->whereDate('created_at', '>=', $from);
                }

                if ($to) {
                    $billingQuery->whereDate('created_at', '<=', $to);
                }
            });

        $medicineSoldQuantity = (clone $medicineSalesQuery)->sum('quantity');
        $medicineSalesAmount = (clone $medicineSalesQuery)->selectRaw('sum(quantity * price) as total')->value('total') ?? 0;
        $medicineInvoiceCount = (clone $medicineSalesQuery)->distinct('billing_id')->count('billing_id');
        $topSellingMedicines = (clone $medicineSalesQuery)
            ->select('service_name', DB::raw('sum(quantity) as sold_quantity'), DB::raw('sum(quantity * price) as sales_amount'))
            ->groupBy('service_name')
            ->orderByDesc('sold_quantity')
            ->limit(10)
            ->get();

        return view('modules.reports.pharmacy', compact(
            'from',
            'to',
            'status',
            'total',
            'lowStock',
            'byStatus',
            'recent',
            'medicineSoldQuantity',
            'medicineSalesAmount',
            'medicineInvoiceCount',
            'topSellingMedicines'
        ));
    }

    public function wardBed(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'beds');

        $wards = Ward::query()
            ->with(['department', 'beds'])
            ->withCount([
                'beds',
                'beds as beds_available_count' => fn ($q) => $q->where('status', 'available'),
                'beds as beds_occupied_count' => fn ($q) => $q->where('status', 'occupied'),
            ])
            ->when(! $dashboardScope->canSeeAll($request->user()), fn ($query) => $query->where('department_id', $request->user()->department_id))
            ->orderBy('name')
            ->get();

        return view('modules.reports.ward-bed', compact('wards'));
    }

    public function staff(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'staff');

        $departmentId = $request->query('department_id');
        $user = $request->user();

        $q = $dashboardScope->staff($user)->with(['user', 'department'])->orderByDesc('id');
        if ($departmentId) {
            if ($dashboardScope->canSeeAll($user) || (int) $departmentId === (int) $user->department_id) {
                $q->where('department_id', $departmentId);
            }
        }

        $staff = $q->paginate(15)->withQueryString();
        $departments = \App\Models\Department::query()
            ->when(! $dashboardScope->canSeeAll($user), fn ($query) => $query->where('id', $user->department_id))
            ->orderBy('name')
            ->get();

        return view('modules.reports.staff', compact('staff', 'departments', 'departmentId'));
    }

    private function authorizeReport(DashboardScopeService $dashboardScope, Request $request, string $key): void
    {
        $visibility = $dashboardScope->visibility($request->user());

        abort_unless($visibility[$key] ?? false, 403);
    }
}
