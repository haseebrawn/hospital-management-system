@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $reportVisibility = $reportVisibility ?? [
            'patients' => false,
            'appointments' => false,
            'lab_tests' => false,
            'revenue' => false,
            'pharmacy' => false,
            'beds' => false,
            'staff' => false,
        ];
    @endphp

    <div class="card">
        <div class="card-title">Reports</div>
        <div class="card-subtitle">Analytics and summaries based on your role and department access.</div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
            @if ($reportVisibility['patients'])
            <a href="{{ route('reports.patients') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Patients</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Counts + recent list</div>
            </a>
            @endif

            @if ($reportVisibility['appointments'])
            <a href="{{ route('reports.appointments') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Appointments</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Status breakdown + recent</div>
            </a>
            @endif

            @if ($reportVisibility['lab_tests'])
            <a href="{{ route('reports.lab-tests') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Lab Tests</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Status breakdown + recent tests</div>
            </a>
            @endif

            @if ($reportVisibility['revenue'])
            <a href="{{ route('reports.billing') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Billing</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Revenue + invoices</div>
            </a>
            @endif

            @if ($reportVisibility['pharmacy'])
            <a href="{{ route('reports.pharmacy') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Pharmacy</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Medicine stock + availability</div>
            </a>
            @endif

            @if ($reportVisibility['beds'])
            <a href="{{ route('reports.ward-bed') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Ward &amp; Bed</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Occupancy</div>
            </a>
            @endif

            @if ($reportVisibility['staff'])
            <a href="{{ route('reports.staff') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Staff</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Staff directory</div>
            </a>
            @endif

            @if (! $reportVisibility['patients'] && ! $reportVisibility['appointments'] && ! $reportVisibility['lab_tests'] && ! $reportVisibility['revenue'] && ! $reportVisibility['pharmacy'] && ! $reportVisibility['beds'] && ! $reportVisibility['staff'])
                <div style="grid-column:1 / -1; padding:16px; border:1px solid var(--border-color); border-radius:14px; color:var(--text-muted);">
                    No reports are available for your current role.
                </div>
            @endif
        </div>
    </div>
@endsection
