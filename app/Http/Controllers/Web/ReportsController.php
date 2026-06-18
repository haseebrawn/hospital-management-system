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
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function __construct(private readonly ReportExportService $exporter)
    {
    }

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
        $departmentId = $request->query('department_id');

        $q = $dashboardScope->patients($request->user());
        if ($departmentId && $dashboardScope->canSeeAll($request->user())) {
            $q->where('department_id', $departmentId);
        }
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
        $chartData = $byGender->map(fn ($row) => ['label' => $row->gender ?: 'unknown', 'value' => (int) $row->total]);

        if ($export = $request->query('export')) {
            $headers = ['MRN', 'Name', 'Phone', 'Gender', 'Created At'];
            $rows = $recent->map(fn ($patient) => [
                $patient->mrn ?? '-',
                trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
                $patient->contact_number ?? '-',
                $patient->gender ?? '-',
                optional($patient->created_at)->format('Y-m-d H:i'),
            ])->toArray();

            return $this->exportReport($export, 'Patient Report', [
                'Total Patients' => $total,
                'Department' => $departmentId ?: 'All',
                'Period From' => $from ?: 'All',
                'Period To' => $to ?: 'All',
            ], $headers, $rows, 'patients_report');
        }

        return view('modules.reports.patients', compact('from', 'to', 'departmentId', 'total', 'byGender', 'recent', 'chartData'));
    }

    public function appointments(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'appointments');

        $from = $request->query('from');
        $to = $request->query('to');
        $departmentId = $request->query('department_id');

        $q = $dashboardScope->appointments($request->user());
        if ($departmentId && $dashboardScope->canSeeAll($request->user())) {
            $q->where('department_id', $departmentId);
        }
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
        $chartData = $byStatus->map(fn ($row) => ['label' => $row->status, 'value' => (int) $row->total]);

        if ($export = $request->query('export')) {
            $headers = ['ID', 'Date', 'Time', 'Patient', 'Reason', 'Doctor', 'Status', 'Visit Flow'];
            $rows = $recent->map(fn ($appointment) => [
                $appointment->id,
                $appointment->date,
                substr((string) $appointment->time, 0, 5),
                trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')),
                $appointment->reason ?: '-',
                optional($appointment->doctor)->name ?? '-',
                $appointment->status,
                $appointment->visit_status,
            ])->toArray();

            return $this->exportReport($export, 'Appointment Report', [
                'Total Appointments' => $total,
                'Department' => $departmentId ?: 'All',
                'Period From' => $from ?: 'All',
                'Period To' => $to ?: 'All',
            ], $headers, $rows, 'appointments_report');
        }

        return view('modules.reports.appointments', compact('from', 'to', 'departmentId', 'total', 'byStatus', 'recent', 'chartData'));
    }

    public function billing(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'revenue');

        $from = $request->query('from');
        $to = $request->query('to');
        $departmentId = $request->query('department_id');

        $q = $dashboardScope->billings($request->user());
        if ($departmentId && $dashboardScope->canSeeAll($request->user())) {
            $q->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $departmentId));
        }
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
        $chartData = $byStatus->map(fn ($row) => ['label' => $row->status, 'value' => (float) $row->amount]);

        if ($export = $request->query('export')) {
            $headers = ['Invoice', 'Patient', 'Total', 'Paid', 'Balance', 'Status', 'Created At'];
            $rows = $recent->map(fn ($invoice) => [
                $invoice->invoice_number ?? ('INV-' . $invoice->id),
                trim((optional($invoice->patient)->first_name ?? '') . ' ' . (optional($invoice->patient)->last_name ?? '')),
                number_format((float) $invoice->total_amount, 2),
                number_format((float) ($invoice->paid_amount ?? 0), 2),
                number_format((float) ($invoice->balance_due ?? 0), 2),
                $invoice->status,
                optional($invoice->created_at)->format('Y-m-d H:i'),
            ])->toArray();

            return $this->exportReport($export, 'Billing Report', [
                'Total Invoices' => $total,
                'Department' => $departmentId ?: 'All',
                'Period From' => $from ?: 'All',
                'Period To' => $to ?: 'All',
                'Revenue' => number_format((float) $sumTotal, 2),
            ], $headers, $rows, 'billing_report');
        }

        return view('modules.reports.billing', compact('from', 'to', 'departmentId', 'total', 'sumTotal', 'byStatus', 'recent', 'chartData'));
    }

    public function labTests(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'lab_tests');

        $from = $request->query('from');
        $to = $request->query('to');
        $departmentId = $request->query('department_id');

        $q = $dashboardScope->labTests($request->user());
        if ($departmentId && $dashboardScope->canSeeAll($request->user())) {
            $q->whereHas('patient', fn ($patientQuery) => $patientQuery->where('department_id', $departmentId));
        }
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
        $chartData = $byStatus->map(fn ($row) => ['label' => $row->status, 'value' => (int) $row->total]);

        if ($export = $request->query('export')) {
            $headers = ['ID', 'Test Type', 'Patient', 'Doctor', 'Technician', 'Status'];
            $rows = $recent->map(fn ($test) => [
                $test->id,
                $test->test_type,
                trim((optional($test->patient)->first_name ?? '') . ' ' . (optional($test->patient)->last_name ?? '')),
                optional($test->doctor)->name ?? '-',
                optional($test->technician)->name ?? '-',
                $test->status,
            ])->toArray();

            return $this->exportReport($export, 'Lab Test Report', [
                'Total Lab Tests' => $total,
                'Department' => $departmentId ?: 'All',
                'Period From' => $from ?: 'All',
                'Period To' => $to ?: 'All',
            ], $headers, $rows, 'lab_tests_report');
        }

        return view('modules.reports.lab-tests', compact('from', 'to', 'departmentId', 'total', 'byStatus', 'recent', 'chartData'));
    }

    public function pharmacy(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'pharmacy');

        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');
        $departmentId = $request->query('department_id');

        $q = Medicine::query();
        if ($status) {
            $q->where('status', $status);
        }
        if ($departmentId && $dashboardScope->canSeeAll($request->user())) {
            $q->where('department_id', $departmentId);
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
        $chartData = $topSellingMedicines->map(fn ($row) => ['label' => $row->service_name, 'value' => (float) $row->sold_quantity]);

        if ($export = $request->query('export')) {
            $headers = ['ID', 'Medicine', 'Stock', 'Price', 'Expiry', 'Status'];
            $rows = $recent->map(fn ($medicine) => [
                $medicine->id,
                $medicine->name,
                $medicine->stock,
                number_format((float) $medicine->price, 2),
                $medicine->expiry_date ?? '-',
                $medicine->status,
            ])->toArray();

            return $this->exportReport($export, 'Pharmacy Report', [
                'Total Medicines' => $total,
                'Low Stock Medicines' => $lowStock,
                'Medicine Quantity Sold' => number_format((int) $medicineSoldQuantity),
                'Medicine Sales Amount' => number_format((float) $medicineSalesAmount, 2),
            ], $headers, $rows, 'pharmacy_report');
        }

        return view('modules.reports.pharmacy', compact(
            'from',
            'to',
            'status',
            'departmentId',
            'total',
            'lowStock',
            'byStatus',
            'recent',
            'medicineSoldQuantity',
            'medicineSalesAmount',
            'medicineInvoiceCount',
            'topSellingMedicines',
            'chartData'
        ));
    }

    public function wardBed(Request $request, DashboardScopeService $dashboardScope)
    {
        $this->authorizeReport($dashboardScope, $request, 'beds');

        $departmentId = $request->query('department_id');

        $wards = Ward::query()
            ->with(['department', 'beds'])
            ->withCount([
                'beds',
                'beds as beds_available_count' => fn ($q) => $q->where('status', 'available'),
                'beds as beds_occupied_count' => fn ($q) => $q->where('status', 'occupied'),
            ])
            ->when($departmentId && $dashboardScope->canSeeAll($request->user()), fn ($query) => $query->where('department_id', $departmentId))
            ->when(! $dashboardScope->canSeeAll($request->user()), fn ($query) => $query->where('department_id', $request->user()->department_id))
            ->orderBy('name')
            ->get();

        if ($export = $request->query('export')) {
            $headers = ['Ward', 'Department', 'Capacity', 'Total Beds', 'Available', 'Occupied'];
            $rows = $wards->map(fn ($ward) => [
                $ward->name,
                optional($ward->department)->name ?? '-',
                $ward->capacity,
                $ward->beds_count,
                $ward->beds_available_count,
                $ward->beds_occupied_count,
            ])->toArray();

            return $this->exportReport($export, 'Ward & Bed Report', [
                'Department' => $departmentId ?: 'All',
                'Wards' => $wards->count(),
            ], $headers, $rows, 'ward_bed_report');
        }

        return view('modules.reports.ward-bed', compact('wards', 'departmentId'));
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

        if ($export = $request->query('export')) {
            $headers = ['Staff ID', 'Name', 'Department', 'Designation', 'Salary', 'Employment Status'];
            $rows = $staff->getCollection()->map(fn ($member) => [
                $member->id,
                optional($member->user)->name ?? '-',
                optional($member->department)->name ?? '-',
                $member->designation,
                $member->salary,
                $member->employment_status,
            ])->toArray();

            return $this->exportReport($export, 'Staff Report', [
                'Department' => $departmentId ?: 'All',
                'Total Staff' => $staff->total(),
            ], $headers, $rows, 'staff_report');
        }

        return view('modules.reports.staff', compact('staff', 'departments', 'departmentId'));
    }

    private function authorizeReport(DashboardScopeService $dashboardScope, Request $request, string $key): void
    {
        $visibility = $dashboardScope->visibility($request->user());

        abort_unless($visibility[$key] ?? false, 403);
    }

    private function exportReport(string $export, string $title, array $summary, array $headers, array $rows, string $filenameBase)
    {
        return match ($export) {
            'csv' => $this->exporter->csv($headers, $rows, $filenameBase . '.csv'),
            'pdf' => $this->exporter->pdf(
                $this->exporter->buildPdfHtml($title, $summary, $headers, $rows),
                $filenameBase . '.pdf'
            ),
            default => abort(400, 'Unsupported export format.'),
        };
    }
}
