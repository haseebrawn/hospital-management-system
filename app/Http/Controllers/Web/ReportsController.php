<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('modules.reports.index');
    }

    public function patients(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $q = Patient::query();
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

    public function appointments(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $q = Appointment::query();
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

    public function billing(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $q = Billing::query();
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

    public function wardBed()
    {
        $wards = Ward::query()
            ->with(['department', 'beds'])
            ->withCount([
                'beds',
                'beds as beds_available_count' => fn ($q) => $q->where('status', 'available'),
                'beds as beds_occupied_count' => fn ($q) => $q->where('status', 'occupied'),
            ])
            ->orderBy('name')
            ->get();

        return view('modules.reports.ward-bed', compact('wards'));
    }

    public function staff(Request $request)
    {
        $departmentId = $request->query('department_id');

        $q = Staff::query()->with(['user', 'department'])->orderByDesc('id');
        if ($departmentId) {
            $q->where('department_id', $departmentId);
        }

        $staff = $q->paginate(15)->withQueryString();
        $departments = \App\Models\Department::query()->orderBy('name')->get();

        return view('modules.reports.staff', compact('staff', 'departments', 'departmentId'));
    }
}
