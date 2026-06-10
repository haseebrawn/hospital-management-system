@extends('layouts.app')

@section('title', 'Lab Test Report')

@section('content')
    <div class="card">
        <div class="card-title">Lab Test Report</div>
        <div class="card-subtitle">Showing lab-test records allowed for your role and department.</div>

        <form method="GET" action="{{ route('reports.lab-tests') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">From</div>
                <input type="date" name="from" value="{{ $from }}"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">To</div>
                <input type="date" name="to" value="{{ $to }}"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                Apply
            </button>
            <a href="{{ route('reports.lab-tests') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Total lab tests</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $total }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">By status</div>
                <div style="margin-top:8px; display:flex; gap:10px; flex-wrap:wrap;">
                    @foreach ($byStatus as $row)
                        <span style="padding:6px 10px; border-radius:999px; border:1px solid var(--border-color); font-size:13px;">
                            {{ ucfirst(str_replace('_', ' ', $row->status)) }}: <b>{{ $row->total }}</b>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Test Type</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Technician</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $test)
                        <tr>
                            <td>{{ $test->id }}</td>
                            <td style="font-weight:700;">{{ $test->test_type }}</td>
                            <td>{{ optional($test->patient)->first_name }} {{ optional($test->patient)->last_name }}</td>
                            <td>{{ optional($test->doctor)->name ?? '-' }}</td>
                            <td>{{ optional($test->technician)->name ?? '-' }}</td>
                            <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $test->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:16px;">No lab tests found.</td>
                        </tr>
                    @endforelse
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
