@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="card">
        <div class="card-title">Reports</div>
        <div class="card-subtitle">Phase 5: Analytics and printable/exportable summaries.</div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
            <a href="{{ route('reports.patients') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Patients</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Counts + recent list</div>
            </a>

            <a href="{{ route('reports.appointments') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Appointments</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Status breakdown + recent</div>
            </a>

            <a href="{{ route('reports.billing') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Billing</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Revenue + invoices</div>
            </a>

            <a href="{{ route('reports.ward-bed') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Ward &amp; Bed</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Occupancy</div>
            </a>

            <a href="{{ route('reports.staff') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Staff</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Staff directory</div>
            </a>
        </div>
    </div>
@endsection
