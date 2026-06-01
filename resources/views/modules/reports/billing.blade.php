@extends('layouts.app')

@section('title', 'Billing Report')

@section('content')
    <div class="card">
        <div class="card-title">Billing Report</div>
        <div class="card-subtitle">Filter by invoice created date range.</div>

        <form method="GET" action="{{ route('reports.billing') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
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
            <a href="{{ route('reports.billing') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Total invoices</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $total }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Total amount</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ number_format((float) $sumTotal, 2) }}</div>
            </div>
        </div>

        <div style="margin-top: 14px; overflow:auto;">
            <table class="dash-table" style="min-width: 760px;">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byStatus as $row)
                        <tr>
                            <td style="text-transform:capitalize; font-weight:700;">{{ $row->status }}</td>
                            <td>{{ $row->total }}</td>
                            <td>{{ number_format((float) $row->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $inv)
                        <tr>
                            <td>{{ $inv->id }}</td>
                            <td>{{ optional($inv->patient)->first_name }} {{ optional($inv->patient)->last_name }}</td>
                            <td>{{ number_format((float) $inv->total_amount, 2) }}</td>
                            <td style="text-transform:capitalize;">{{ $inv->status }}</td>
                            <td>{{ optional($inv->created_at)->format('Y-m-d H:i') }}</td>
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

