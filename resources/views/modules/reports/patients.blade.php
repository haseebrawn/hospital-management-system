@extends('layouts.app')

@section('title', 'Patient Report')

@section('content')
    <div class="card">
        <div class="card-title">Patient Report</div>
        <div class="card-subtitle">Filter scoped patient records by created date range.</div>

        <form method="GET" action="{{ route('reports.patients') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
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
            <a href="{{ route('reports.patients') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
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
                            {{ ucfirst($row->gender) }}: <b>{{ $row->total }}</b>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

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
                    @foreach ($recent as $p)
                        <tr>
                            <td style="font-weight:700;">{{ $p->mrn ?? '-' }}</td>
                            <td style="font-weight:700;">{{ $p->first_name }} {{ $p->last_name }}</td>
                            <td>{{ $p->contact_number }}</td>
                            <td style="text-transform:capitalize;">{{ $p->gender }}</td>
                            <td>{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
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
