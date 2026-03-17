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

</div>
@endsection