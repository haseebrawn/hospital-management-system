<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Billing;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary Metrics
        $totalPatients = Patient::count();
        $newAppointments = Appointment::whereDate('created_at', now()->toDateString())->count();
        $labTestsPending = 12; // replace with real logic if LabTest model exists
        $todaysRevenue = Billing::whereDate('created_at', now()->toDateString())
            ->sum('total_amount');

        // Recent Appointments
        $recentAppointments = Appointment::with('patient', 'doctor')
            ->orderByDesc('scheduled_at')
            ->limit(5)
            ->get();

        // Bed Status
        $availableBeds = Bed::where('status', 'available')->count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();

        // Notifications sample
        $notifications = [
            ['name'=>'Sarah Ahmed','message'=>'New patient registered','status'=>'upcoming'],
            ['name'=>'Ahsan Raza','message'=>'Appointment checked-in','status'=>'checked-in'],
            ['name'=>'Suhail Farooq','message'=>'Appointment cancelled','status'=>'cancelled'],
        ];

        // Pass all data to blade
        return view('dashboard', compact(
            'totalPatients','newAppointments','labTestsPending','todaysRevenue',
            'recentAppointments','availableBeds','occupiedBeds','notifications'
        ));
    }
}