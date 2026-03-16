@extends('layouts.app')

@section('title', 'Dashboard - Hospital HMS')

@section('content')
<style>
/* Inline CSS to match your screenshot */
.dashboard-container { display: flex; gap: 20px; flex-wrap: wrap; }
.summary-card { flex: 1; min-width: 180px; padding: 20px; color: #fff; border-radius: 10px; }
.bg-blue { background-color: #3b82f6; }
.bg-green { background-color: #10b981; }
.bg-yellow { background-color: #facc15; color: #000; }
.bg-purple { background-color: #8b5cf6; }

.card { background: #fff; border-radius: 10px; padding: 20px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);}
.card h3 { margin-bottom: 10px; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border-bottom: 1px solid #e5e7eb; padding: 10px; text-align: left; }
.badge { padding: 4px 8px; border-radius: 6px; font-size: 12px; color:#fff;}
.badge-upcoming { background-color: #10b981; }
.badge-checked-in { background-color: #3b82f6; }
.badge-cancelled { background-color: #ef4444; }

.flex { display: flex; gap: 20px; }
.chart-placeholder { height: 150px; background: #f3f4f6; border-radius: 8px; display:flex; align-items:center; justify-content:center; color:#6b7280; font-weight:bold; }

.notification { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #e5e7eb; }
</style>

<div>
    <h2>Welcome, {{ auth()->user()->name }}</h2>
    <p style="color:#6b7280;">You are logged in as <strong>{{ optional(auth()->user()->department)->name ?? 'Staff Member' }}</strong></p>

    <!-- Summary Cards -->
    <div class="dashboard-container">
        <div class="summary-card bg-blue">Total Patients<br><strong>{{ $totalPatients }}</strong></div>
        <div class="summary-card bg-green">New Appointments<br><strong>{{ $newAppointments }}</strong></div>
        <div class="summary-card bg-yellow">Lab Tests Pending<br><strong>{{ $labTestsPending }}</strong></div>
        <div class="summary-card bg-purple">Today's Revenue<br><strong>₹{{ number_format($todaysRevenue,2) }}</strong></div>
    </div>

    <div class="flex">
        <!-- Recent Appointments -->
        <div class="card" style="flex:2">
            <h3>Recent Appointments</h3>
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Doctor</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentAppointments as $a)
                        <tr>
                            <td>{{ $a->patient->name ?? 'N/A' }}</td>
                            <td>{{ $a->doctor->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $status = strtolower($a->status ?? 'upcoming');
                                    $class = 'badge-upcoming';
                                    if($status=='checked-in') $class='badge-checked-in';
                                    if($status=='cancelled') $class='badge-cancelled';
                                @endphp
                                <span class="badge {{ $class }}">{{ ucfirst($status) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bed Status -->
        <div class="card" style="flex:1">
            <h3>Bed Status</h3>
            <p>Available: {{ $availableBeds }}</p>
            <p>Occupied: {{ $occupiedBeds }}</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="flex">
        <div class="card chart-placeholder" style="flex:1">Patients Overview Chart</div>
        <div class="card chart-placeholder" style="flex:1">Hospital Revenue Chart</div>
        <div class="card chart-placeholder" style="flex:1">Appointment Overview Chart</div>
    </div>

    <!-- Notifications -->
    <div class="card">
        <h3>Notifications</h3>
        @foreach($notifications as $n)
            <div class="notification">
                <div>
                    <strong>{{ $n['name'] }}</strong>: {{ $n['message'] }}
                </div>
                <div class="badge 
                    @if($n['status']=='upcoming') badge-upcoming 
                    @elseif($n['status']=='checked-in') badge-checked-in 
                    @else badge-cancelled @endif">
                    {{ ucfirst($n['status']) }}
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection