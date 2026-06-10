@extends('layouts.app')

@section('title', 'Pharmacy Report')

@section('content')
    <div class="card">
        <div class="card-title">Pharmacy Report</div>
        <div class="card-subtitle">Medicine stock, availability, and low-stock overview.</div>

        <form method="GET" action="{{ route('reports.pharmacy') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Sales From</div>
                <input type="date" name="from" value="{{ $from }}"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Sales To</div>
                <input type="date" name="to" value="{{ $to }}"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Status</div>
                <select name="status"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff; min-width:220px;">
                    <option value="">All statuses</option>
                    @foreach (['available', 'unavailable'] as $option)
                        <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>
                            {{ ucfirst($option) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                Apply
            </button>
            <a href="{{ route('reports.pharmacy') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 14px; display:grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Total medicines</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $total }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Low stock medicines</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $lowStock }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Medicine quantity sold</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ number_format((int) $medicineSoldQuantity) }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Medicine sales amount</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ number_format((float) $medicineSalesAmount, 2) }}</div>
                <div style="font-size:11px; color: var(--text-muted); margin-top:3px;">From {{ number_format((int) $medicineInvoiceCount) }} paid invoices</div>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 0.8fr 1.2fr; gap:14px;">
            <div style="overflow:auto;">
                <table class="dash-table" style="min-width: 360px;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byStatus as $row)
                            <tr>
                                <td style="text-transform:capitalize; font-weight:700;">{{ $row->status }}</td>
                                <td>{{ $row->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="overflow:auto;">
                <table class="dash-table" style="min-width: 560px;">
                    <thead>
                        <tr>
                            <th>Top Selling Medicine</th>
                            <th>Sold Qty</th>
                            <th>Sales Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topSellingMedicines as $medicineSale)
                            <tr>
                                <td style="font-weight:700;">{{ $medicineSale->service_name }}</td>
                                <td>{{ number_format((int) $medicineSale->sold_quantity) }}</td>
                                <td>{{ number_format((float) $medicineSale->sales_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding:16px;">No paid medicine sales found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Expiry</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $medicine)
                        <tr>
                            <td>{{ $medicine->id }}</td>
                            <td style="font-weight:700;">{{ $medicine->name }}</td>
                            <td>{{ $medicine->stock }}</td>
                            <td>{{ number_format((float) $medicine->price, 2) }}</td>
                            <td>{{ $medicine->expiry_date ?? '-' }}</td>
                            <td style="text-transform:capitalize;">{{ $medicine->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:16px;">No medicines found.</td>
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
