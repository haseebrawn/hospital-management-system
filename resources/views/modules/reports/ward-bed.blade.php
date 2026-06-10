@extends('layouts.app')

@section('title', 'Ward & Bed Report')

@section('content')
    <div class="card">
        <div class="card-title">Ward &amp; Bed Report</div>
        <div class="card-subtitle">Occupancy overview based on your department access.</div>

        <div style="margin-top: 14px; overflow:auto;">
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
                    @foreach ($wards as $w)
                        <tr>
                            <td style="font-weight:700;">{{ $w->name }}</td>
                            <td>{{ optional($w->department)->name ?? '-' }}</td>
                            <td>{{ $w->capacity }}</td>
                            <td>{{ $w->beds_count }}</td>
                            <td>{{ $w->beds_available_count }}</td>
                            <td>{{ $w->beds_occupied_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            <a href="{{ route('reports.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to reports
            </a>
        </div>
    </div>
@endsection
