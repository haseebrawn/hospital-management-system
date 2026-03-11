@extends('layouts.app')

@section('title', 'Dashboard - Hospital HMS')

@section('content')
    <div class="card-title">Welcome, {{ auth()->user()->name }}</div>
    <div class="card-subtitle">
        This is your hospital management dashboard. From here you will be able to manage patients,
        appointments, billing, and more once the corresponding modules are connected to the web UI.
    </div>

    <div style="font-size: 13px; color: #6b7280;">
        You are logged in as
        <strong>{{ optional(auth()->user()->department)->name ?? 'Staff Member' }}</strong>.
        Use the profile icon in the top-right corner to open the small popup and logout.
    </div>
@endsection

