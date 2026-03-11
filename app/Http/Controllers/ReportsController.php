<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportExportService;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\LabTest;
use App\Models\Ward;
use App\Models\Bed;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    protected ReportExportService $exporter;

    public function __construct(ReportExportService $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * Helper: parse from / to
     */
    protected function parseDates(Request $request)
    {
        $from = $request->query('from') ? date('Y-m-d', strtotime($request->query('from'))) : null;
        $to = $request->query('to') ? date('Y-m-d', strtotime($request->query('to'))) : null;
        return [$from, $to];
    }

    /**
     * 1) Patient report
     * GET /reports/patients?from=2025-01-01&to=2025-10-01&export=csv
     */
    public function patientReport(Request $request)
    {
        [$from, $to] = $this->parseDates($request);

        $q = Patient::query();

        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to) $q->whereDate('created_at', '<=', $to);

        $total = $q->count();

        $byGender = $q->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')->get();

        $recent = $q->orderByDesc('created_at')->limit(50)->get();

        $chartData = [
            'labels' => $byGender->pluck('gender')->toArray(),
            'data' => $byGender->pluck('total')->toArray(),
        ];

        // Export
        $export = $request->query('export');
        if ($export === 'csv') {
            $headers = ['ID','Name','Email','Gender','DOB','Created At'];
            $rows = $recent->map(function ($p) {
                return [
                    $p->id,
                    $p->name,
                    $p->email,
                    $p->gender ?? 'N/A',
                    $p->date_of_birth ?? '',
                    $p->created_at,
                ];
            })->toArray();

            return $this->exporter->csv($headers, $rows, 'patients_report.csv');
        }

        if ($export === 'pdf') {
            $summary = [
                'Total Patients' => $total,
                'Period From' => $from ?? 'All',
                'Period To' => $to ?? 'All',
            ];
            $columns = ['ID','Name','Email','Gender','DOB','Created At'];
            $rows = $recent->map(fn($p)=>[$p->id,$p->name,$p->email,$p->gender ?? 'N/A',$p->date_of_birth ?? '',$p->created_at])->toArray();
            $html = $this->exporter->buildPdfHtml('Patient Report', $summary, $columns, $rows);
            return $this->exporter->pdf($html, 'patients_report.pdf');
        }

        return response()->json([
            'total' => $total,
            'by_gender' => $byGender,
            'recent' => $recent,
            'chart_data' => $chartData,
        ]);
    }

    /**
     * 2) Appointment report
     */
    public function appointmentReport(Request $request)
    {
        [$from, $to] = $this->parseDates($request);

        $q = Appointment::query();
        if ($from) $q->whereDate('scheduled_at', '>=', $from);
        if ($to) $q->whereDate('scheduled_at', '<=', $to);

        $total = $q->count();
        $byStatus = $q->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();

        // monthly trend for last 12 months
        $trend = Appointment::select(
            DB::raw("DATE_FORMAT(scheduled_at, '%Y-%m') as month"),
            DB::raw('count(*) as total')
        )
        ->when($from, fn($q) => $q->whereDate('scheduled_at', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('scheduled_at', '<=', $to))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Export
        $export = $request->query('export');
        if ($export === 'csv') {
            $headers = ['ID','Patient','Doctor','Scheduled At','Status'];
            $rows = $q->with(['patient','doctor'])->orderByDesc('scheduled_at')->limit(100)->get()
                ->map(fn($a)=>[$a->id, $a->patient->name ?? '', $a->doctor->name ?? '', $a->scheduled_at, $a->status])->toArray();
            return $this->exporter->csv($headers, $rows, 'appointments_report.csv');
        }
        if ($export === 'pdf') {
            $summary = ['Total Appointments' => $total, 'Period From' => $from ?? 'All', 'Period To' => $to ?? 'All'];
            $columns = ['ID','Patient','Doctor','Scheduled At','Status'];
            $rows = $q->with(['patient','doctor'])->orderByDesc('scheduled_at')->limit(100)->get()
                ->map(fn($a)=>[$a->id,$a->patient->name ?? '',$a->doctor->name ?? '',$a->scheduled_at,$a->status])->toArray();
            $html = $this->exporter->buildPdfHtml('Appointment Report', $summary, $columns, $rows);
            return $this->exporter->pdf($html, 'appointments_report.pdf');
        }

        return response()->json([
            'total' => $total,
            'by_status' => $byStatus,
            'trend' => $trend,
        ]);
    }

    /**
     * 3) Billing & Finance report
     */
    public function billingReport(Request $request)
    {
        [$from, $to] = $this->parseDates($request);

        $q = Billing::query();
        if ($from) $q->whereDate('created_at','>=',$from);
        if ($to) $q->whereDate('created_at','<=',$to);

        $totalRevenue = (float) $q->where('status','paid')->sum('total_amount');
        $pending = (float) $q->where('status','pending')->sum('total_amount');
        $count = $q->count();

        // breakdown by day
        $daily = Billing::select(
            DB::raw("DATE(created_at) as date"),
            DB::raw('SUM(total_amount) as total')
        )
        ->when($from, fn($q)=>$q->whereDate('created_at','>=',$from))
        ->when($to, fn($q)=>$q->whereDate('created_at','<=',$to))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $export = $request->query('export');
        if ($export === 'csv') {
            $items = Billing::with('patient')->orderByDesc('created_at')->limit(200)->get();
            $headers = ['Invoice ID','Patient','Total','Status','Created At'];
            $rows = $items->map(fn($b)=>[$b->id, $b->patient->name ?? '', $b->total_amount, $b->status, $b->created_at])->toArray();
            return $this->exporter->csv($headers, $rows, 'billing_report.csv');
        }
        if ($export === 'pdf') {
            $summary = ['Total Revenue' => number_format($totalRevenue,2), 'Pending' => number_format($pending,2), 'Count' => $count];
            $columns = ['Invoice ID','Patient','Total','Status','Created At'];
            $rows = Billing::with('patient')->orderByDesc('created_at')->limit(200)->get()
                ->map(fn($b)=>[$b->id, $b->patient->name ?? '', $b->total_amount, $b->status, $b->created_at])->toArray();
            $html = $this->exporter->buildPdfHtml('Billing & Revenue Report', $summary, $columns, $rows);
            return $this->exporter->pdf($html, 'billing_report.pdf');
        }

        return response()->json([
            'total_revenue' => $totalRevenue,
            'pending_amount' => $pending,
            'daily' => $daily,
        ]);
    }

    /**
     * 4) Pharmacy report (stock & expired)
     */
    public function pharmacyReport(Request $request)
    {
        [$from, $to] = $this->parseDates($request);

        $lowStockThreshold = (int) $request->query('threshold', 10);

        $lowStock = Medicine::where('stock','<=',$lowStockThreshold)->get();
        $expired = Medicine::whereDate('expiry_date','<',now()->toDateString())->get();

        // usage stats (how many times medicine included in prescriptions)
        $usage = DB::table('prescription_medicines')
            ->select('medicine_id', DB::raw('count(*) as total'))
            ->groupBy('medicine_id')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $export = $request->query('export');
        if ($export === 'csv') {
            $headers = ['Medicine ID','Name','Stock','Expiry Date','Status'];
            $rows = Medicine::orderBy('stock')->get()->map(fn($m)=>[$m->id,$m->name,$m->stock,$m->expiry_date,$m->status])->toArray();
            return $this->exporter->csv($headers, $rows, 'pharmacy_report.csv');
        }
        if ($export === 'pdf') {
            $summary = ['Low stock threshold' => $lowStockThreshold, 'Low Stock Count' => $lowStock->count(), 'Expired Count' => $expired->count()];
            $columns = ['ID','Name','Stock','Expiry Date','Status'];
            $rows = Medicine::orderBy('stock')->get()->map(fn($m)=>[$m->id,$m->name,$m->stock,$m->expiry_date,$m->status])->toArray();
            $html = $this->exporter->buildPdfHtml('Pharmacy Stock Report', $summary, $columns, $rows);
            return $this->exporter->pdf($html, 'pharmacy_report.pdf');
        }

        return response()->json([
            'low_stock' => $lowStock,
            'expired' => $expired,
            'usage' => $usage,
        ]);
    }

    /**
     * 5) Lab tests report
     */
    public function labReport(Request $request)
    {
        [$from, $to] = $this->parseDates($request);

        $q = LabTest::query();
        if ($from) $q->whereDate('created_at','>=',$from);
        if ($to) $q->whereDate('created_at','<=',$to);

        $byStatus = $q->select('status',DB::raw('count(*) as total'))->groupBy('status')->get();
        $recent = $q->with(['patient','technician'])->orderByDesc('created_at')->limit(200)->get();

        $export = $request->query('export');
        if ($export === 'csv') {
            $headers = ['ID','Test','Patient','Technician','Status','Created At'];
            $rows = $recent->map(fn($t)=>[$t->id,$t->test_type,$t->patient->name ?? '',$t->technician->name ?? '',$t->status,$t->created_at])->toArray();
            return $this->exporter->csv($headers, $rows, 'lab_report.csv');
        }
        if ($export === 'pdf') {
            $summary = ['Total' => $q->count()];
            $columns = ['ID','Test','Patient','Technician','Status','Created At'];
            $rows = $recent->map(fn($t)=>[$t->id,$t->test_type,$t->patient->name ?? '',$t->technician->name ?? '',$t->status,$t->created_at])->toArray();
            $html = $this->exporter->buildPdfHtml('Lab Tests Report', $summary, $columns, $rows);
            return $this->exporter->pdf($html, 'lab_report.pdf');
        }

        return response()->json([
            'by_status' => $byStatus,
            'recent' => $recent,
        ]);
    }

    /**
     * 6) Ward & Bed occupancy report
     */
    public function wardBedReport(Request $request)
    {
        $wards = Ward::with(['beds','beds.allocations'])->get();

        $rows = [];
        foreach ($wards as $ward) {
            $totalBeds = $ward->beds->count();
            $occupied = $ward->beds->where('status','occupied')->count();
            $available = $ward->beds->where('status','available')->count();
            $rows[] = [
                'ward_id' => $ward->id,
                'ward' => $ward->name,
                'department' => $ward->department?->name,
                'capacity' => $ward->capacity,
                'total_beds' => $totalBeds,
                'occupied' => $occupied,
                'available' => $available,
                'occupancy_rate' => $totalBeds ? round($occupied / $totalBeds * 100, 2) : 0
            ];
        }

        $export = $request->query('export');
        if ($export === 'csv') {
            $headers = ['Ward ID','Ward','Department','Capacity','Total Beds','Occupied','Available','Occupancy Rate'];
            $rowsCsv = array_map(fn($r)=>[$r['ward_id'],$r['ward'],$r['department'],$r['capacity'],$r['total_beds'],$r['occupied'],$r['available'],$r['occupancy_rate']], $rows);
            return $this->exporter->csv($headers, $rowsCsv, 'ward_bed_report.csv');
        }
        if ($export === 'pdf') {
            $summary = ['Wards' => count($wards)];
            $columns = ['Ward ID','Ward','Department','Capacity','Total Beds','Occupied','Available','Occupancy Rate'];
            $rowsPdf = array_map(fn($r)=>[$r['ward_id'],$r['ward'],$r['department'],$r['capacity'],$r['total_beds'],$r['occupied'],$r['available'],$r['occupancy_rate']], $rows);
            $html = $this->exporter->buildPdfHtml('Ward & Bed Occupancy Report', $summary, $columns, $rowsPdf);
            return $this->exporter->pdf($html, 'ward_bed_report.pdf');
        }

        return response()->json([
            'wards' => $rows
        ]);
    }

    /**
     * 7) Staff & shift report (attendance / shifts)
     */
    public function staffReport(Request $request)
    {
        [$from, $to] = $this->parseDates($request);

        $q = Staff::with('user','department','shifts');

        if ($request->query('department_id')) {
            $q->where('department_id', $request->query('department_id'));
        }

        $staffs = $q->get();

        // compute basic metrics
        $totalStaff = $staffs->count();
        $onDuty = Staff::whereHas('shifts', function($q) use ($from, $to) {
            if ($from) $q->whereDate('shift_date','>=',$from);
            if ($to) $q->whereDate('shift_date','<=',$to);
        })->count();

        $export = $request->query('export');
        if ($export === 'csv') {
            $headers = ['Staff ID','Name','Department','Designation','Salary','Employment Status'];
            $rows = $staffs->map(fn($s)=>[$s->id,$s->user->name ?? '', $s->department->name ?? '', $s->designation, $s->salary, $s->employment_status])->toArray();
            return $this->exporter->csv($headers, $rows, 'staff_report.csv');
        }
        if ($export === 'pdf') {
            $summary = ['Total Staff' => $totalStaff, 'On Duty (range)' => $onDuty];
            $columns = ['Staff ID','Name','Department','Designation','Salary','Employment Status'];
            $rows = $staffs->map(fn($s)=>[$s->id,$s->user->name ?? '', $s->department->name ?? '', $s->designation, $s->salary, $s->employment_status])->toArray();
            $html = $this->exporter->buildPdfHtml('Staff & Shift Report', $summary, $columns, $rows);
            return $this->exporter->pdf($html, 'staff_report.pdf');
        }

        return response()->json([
            'total_staff' => $totalStaff,
            'on_duty' => $onDuty,
            'staff' => $staffs,
        ]);
    }
}
