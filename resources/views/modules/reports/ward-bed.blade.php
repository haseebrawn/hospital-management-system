@extends('layouts.app')

@section('title', 'Ward & Bed Report')

@section('content')
    <style>
        @media print {
            .report-actions, .report-nav, .report-form {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>

    <div class="card">
        <div class="card-title">Ward &amp; Bed Report</div>
        <div class="card-subtitle">Occupancy overview based on your department access.</div>

        <div class="report-actions" style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap; margin-top: 10px;">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); text-decoration:none; color:inherit; background:#fff;">Export CSV</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); text-decoration:none; color:inherit; background:#fff;">Export PDF</a>
            <button type="button" onclick="window.print(); return false;" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">Print</button>
        </div>

        <form method="GET" action="{{ route('reports.ward-bed') }}" class="report-form" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-top: 12px;">
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Department</div>
                <input type="text" name="department_id" value="{{ $departmentId ?? '' }}" placeholder="Department ID"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:220px;">
            </div>
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                Apply
            </button>
            <a href="{{ route('reports.ward-bed') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>Ward</th>
                        <th>Department</th>
                        <th>Capacity</th>
                        <th>Total beds</th>
                        <th>Available</th>
                        <th>Occupied</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($wards as $w)
                        <tr>
                            <td style="font-weight:700;">{{ $w->name }}</td>
                            <td>{{ optional($w->department)->name ?? '-' }}</td>
                            <td>{{ $w->capacity }}</td>
                            <td>{{ $w->beds_count }}</td>
                            <td>{{ $w->beds_available_count }}</td>
                            <td>{{ $w->beds_occupied_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:16px;">No wards found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-nav" style="margin-top: 14px;">
            <a href="{{ route('reports.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to reports
            </a>
        </div>
    </div>
@endsection
