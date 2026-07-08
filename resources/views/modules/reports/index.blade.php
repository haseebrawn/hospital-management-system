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

        <div class="report-grid">
            @if ($reportVisibility['patients'])
            <a href="{{ route('reports.patients') }}" class="report-tile">
                <div class="report-tile__title">Patients</div>
                <div class="report-tile__desc">Counts + recent list</div>
            </a>
            @endif

            @if ($reportVisibility['appointments'])
            <a href="{{ route('reports.appointments') }}" class="report-tile">
                <div class="report-tile__title">Appointments</div>
                <div class="report-tile__desc">Status breakdown + recent</div>
            </a>
            @endif

            @if ($reportVisibility['lab_tests'])
            <a href="{{ route('reports.lab-tests') }}" class="report-tile">
                <div class="report-tile__title">Lab Tests</div>
                <div class="report-tile__desc">Status breakdown + recent tests</div>
            </a>
            @endif

            @if ($reportVisibility['revenue'])
            <a href="{{ route('reports.billing') }}" class="report-tile">
                <div class="report-tile__title">Billing</div>
                <div class="report-tile__desc">Revenue + invoices</div>
            </a>
            @endif

            @if ($reportVisibility['pharmacy'])
            <a href="{{ route('reports.pharmacy') }}" class="report-tile">
                <div class="report-tile__title">Pharmacy</div>
                <div class="report-tile__desc">Medicine stock + availability</div>
            </a>
            @endif

            @if ($reportVisibility['beds'])
            <a href="{{ route('reports.ward-bed') }}" class="report-tile">
                <div class="report-tile__title">Ward &amp; Bed</div>
                <div class="report-tile__desc">Occupancy</div>
            </a>
            @endif

            @if ($reportVisibility['staff'])
            <a href="{{ route('reports.staff') }}" class="report-tile">
                <div class="report-tile__title">Staff</div>
                <div class="report-tile__desc">Staff directory</div>
            </a>
            @endif

            @if (! $reportVisibility['patients'] && ! $reportVisibility['appointments'] && ! $reportVisibility['lab_tests'] && ! $reportVisibility['revenue'] && ! $reportVisibility['pharmacy'] && ! $reportVisibility['beds'] && ! $reportVisibility['staff'])
                <div style="grid-column:1 / -1; padding:16px; border:1px solid var(--border-color); border-radius:14px; color:var(--text-muted); background:#fff;">
                    No reports are available for your current role.
                </div>
            @endif
        </div>
    </div>
@endsection
