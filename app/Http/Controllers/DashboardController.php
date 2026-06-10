<?php

namespace App\Http\Controllers;

use App\Models\BillingItem;
use App\Models\Medicine;
use App\Services\DashboardScopeService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(DashboardScopeService $dashboardScope)
    {
        $user = Auth::user();
        $dashboardVisibility = $dashboardScope->visibility($user);

        // Summary Metrics
        $totalPatients = $dashboardVisibility['patients'] ? $dashboardScope->patients($user)->count() : 0;
        $newAppointments = $dashboardVisibility['appointments']
            ? $dashboardScope->appointments($user)->whereDate('created_at', now()->toDateString())->count()
            : 0;
        $todaysAppointments = $dashboardVisibility['appointments']
            ? $dashboardScope->appointments($user)->whereDate('date', now()->toDateString())->count()
            : 0;
        $pendingAppointments = $dashboardVisibility['appointments']
            ? $dashboardScope->appointments($user)->where('status', 'pending')->count()
            : 0;
        $approvedAppointments = $dashboardVisibility['appointments']
            ? $dashboardScope->appointments($user)->where('status', 'approved')->count()
            : 0;
        $labTestsPending = $dashboardVisibility['lab_tests']
            ? $dashboardScope->labTests($user)->where('status', 'pending')->count()
            : 0;
        $todaysRevenue = $dashboardVisibility['revenue']
            ? $dashboardScope->billings($user)->whereDate('created_at', now()->toDateString())->sum('total_amount')
            : 0;
        $pendingInvoices = $dashboardVisibility['revenue']
            ? $dashboardScope->billings($user)->where('status', 'pending')->count()
            : 0;
        $lowStockMedicines = $dashboardVisibility['pharmacy'] ? Medicine::query()->where('stock', '<=', 10)->count() : 0;
        $medicineSalesQuery = BillingItem::query()
            ->where('type', 'medicine')
            ->whereHas('billing', fn ($billingQuery) => $billingQuery->where('status', 'paid'));
        $medicineSoldQuantity = $dashboardVisibility['pharmacy'] ? (clone $medicineSalesQuery)->sum('quantity') : 0;
        $medicineSalesAmount = $dashboardVisibility['pharmacy']
            ? ((clone $medicineSalesQuery)->selectRaw('sum(quantity * price) as total')->value('total') ?? 0)
            : 0;
        $activeStaff = $dashboardVisibility['staff'] ? $dashboardScope->staff($user)->where('employment_status', 'active')->count() : 0;

        // Recent Appointments
        $recentAppointments = $dashboardVisibility['appointments']
            ? $dashboardScope->appointments($user)
            ->with('patient', 'doctor')
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(5)
            ->get()
            : collect();

        // Bed Status
        $availableBeds = $dashboardVisibility['beds'] ? $dashboardScope->beds($user)->where('status', 'available')->count() : 0;
        $occupiedBeds = $dashboardVisibility['beds'] ? $dashboardScope->beds($user)->where('status', 'occupied')->count() : 0;

        // Notifications sample
        $notifications = [
            ['name'=>'Sarah Ahmed','message'=>'New patient registered','status'=>'upcoming'],
            ['name'=>'Ahsan Raza','message'=>'Appointment checked-in','status'=>'checked-in'],
            ['name'=>'Suhail Farooq','message'=>'Appointment cancelled','status'=>'cancelled'],
        ];

        // Pass all data to blade
        return view('dashboard', compact(
            'totalPatients','newAppointments','todaysAppointments','pendingAppointments','approvedAppointments','labTestsPending','todaysRevenue','pendingInvoices',
            'recentAppointments','availableBeds','occupiedBeds','notifications', 'dashboardVisibility',
            'lowStockMedicines', 'medicineSoldQuantity', 'medicineSalesAmount', 'activeStaff'
        ));
    }
}
