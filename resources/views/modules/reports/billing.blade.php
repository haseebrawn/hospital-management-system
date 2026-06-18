@extends('layouts.app')

@section('title', 'Billing Report')

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
        <div class="card-title">Billing Report</div>
        <div class="card-subtitle">Filter scoped invoices by invoice created date range.</div>

        <div class="report-actions" style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap; margin-top: 10px;">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); text-decoration:none; color:inherit; background:#fff;">Export CSV</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); text-decoration:none; color:inherit; background:#fff;">Export PDF</a>
            <button type="button" onclick="window.print(); return false;" style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">Print</button>
        </div>

        <form method="GET" action="{{ route('reports.billing') }}" class="report-form" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-top: 12px;">
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
            <a href="{{ route('reports.billing', array_filter(['department_id' => $departmentId])) }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
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

        @includeWhen(isset($chartData), 'modules.reports._simple-chart', ['chartTitle' => 'Revenue by Status', 'chartData' => $chartData])

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
                        <th>Invoice</th>
                        <th>Patient</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $inv)
                        <tr>
                            <td style="font-weight:700;">{{ $inv->invoice_number ?? ('INV-' . $inv->id) }}</td>
                            <td>{{ optional($inv->patient)->first_name }} {{ optional($inv->patient)->last_name }}</td>
                            <td>{{ number_format((float) $inv->total_amount, 2) }}</td>
                            <td>{{ number_format((float) ($inv->paid_amount ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($inv->balance_due ?? 0), 2) }}</td>
                            <td style="text-transform:capitalize;">{{ $inv->status }}</td>
                            <td>{{ optional($inv->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:16px;">No invoices found.</td>
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
