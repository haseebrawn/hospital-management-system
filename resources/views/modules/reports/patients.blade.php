@extends('layouts.app')

@section('title', 'Patient Report')

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
        <div class="card-title">Patient Report</div>
        <div class="card-subtitle">Filter scoped patient records by created date range.</div>

        <div class="report-actions" style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap; margin-top: 10px;">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); text-decoration:none; color:inherit; background:#fff;">Export CSV</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); text-decoration:none; color:inherit; background:#fff;">Export PDF</a>
            <button type="button" onclick="window.print(); return false;" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">Print</button>
        </div>

        <form method="GET" action="{{ route('reports.patients') }}" class="report-form" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-top: 12px;">
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
            @isset($departmentId)
                <input type="hidden" name="department_id" value="{{ $departmentId }}">
            @endisset
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                Apply
            </button>
            <a href="{{ route('reports.patients', array_filter(['department_id' => $departmentId])) }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Total patients</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $total }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">By gender</div>
                <div style="margin-top:8px; display:flex; gap:10px; flex-wrap:wrap;">
                    @foreach ($byGender as $row)
                        <span style="padding:6px 10px; border-radius:999px; border:1px solid var(--border-color); font-size:13px;">
                            {{ ucfirst($row->gender ?? 'unknown') }}: <b>{{ $row->total }}</b>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        @includeWhen(isset($chartData), 'modules.reports._simple-chart', ['chartTitle' => 'Gender Breakdown', 'chartData' => $chartData])

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 920px;">
                <thead>
                    <tr>
                        <th>MRN</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $p)
                        <tr>
                            <td style="font-weight:700;">{{ $p->mrn ?? '-' }}</td>
                            <td style="font-weight:700;">{{ $p->first_name }} {{ $p->last_name }}</td>
                            <td>{{ $p->contact_number }}</td>
                            <td style="text-transform:capitalize;">{{ $p->gender }}</td>
                            <td>{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:16px;">No patients found.</td>
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
