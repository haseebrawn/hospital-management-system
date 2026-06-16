@extends('layouts.app')

@section('title', 'Printable Receipt')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Receipt {{ $billing->invoice_number ?? ('#' . $billing->id) }}</div>
                <div class="card-subtitle">Printable billing receipt.</div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="button" onclick="window.print()"
                    style="padding:8px 12px; border-radius:10px; border:none; background:var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                    Print
                </button>
                <a href="{{ route('billing.show', $billing) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Back
                </a>
            </div>
        </div>

        <div style="margin-top:16px; padding:16px; border:1px solid var(--border-color); border-radius:16px;">
            <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                <div>
                    <div style="font-weight:800; font-size:18px;">Hospital HMS</div>
                    <div style="font-size:13px; color:var(--text-muted);">Invoice receipt</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:700;">{{ $billing->invoice_number ?? ('#' . $billing->id) }}</div>
                    <div style="font-size:13px; color:var(--text-muted);">{{ optional($billing->created_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>

            <div style="margin-top:14px; display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Patient</div>
                    <div style="font-weight:700;">{{ optional($billing->patient)->first_name }} {{ optional($billing->patient)->last_name }}</div>
                    <div style="font-size:13px; color:var(--text-muted);">{{ optional($billing->patient)->contact_number }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Payment</div>
                    <div style="font-weight:700; text-transform:capitalize;">{{ $billing->payment_method ?: '-' }}</div>
                    <div style="font-size:13px; color:var(--text-muted);">Status: {{ $billing->status }}</div>
                </div>
            </div>

            <div style="margin-top:16px; overflow:auto;">
                <table class="dash-table" style="min-width:900px;">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($billing->items as $item)
                            <tr>
                                <td>{{ $item->service_name }}</td>
                                <td style="text-transform:capitalize;">{{ $item->type }}</td>
                                <td>{{ $item->source_type ?: '-' }} {{ $item->source_id ? '#'.$item->source_id : '' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format((float) $item->price, 2) }}</td>
                                <td>{{ number_format((float) $item->price * (int) $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                <div style="min-width:260px;">
                    <div style="display:flex; justify-content:space-between;"><span>Total</span><strong>{{ number_format((float) $billing->total_amount, 2) }}</strong></div>
                    <div style="display:flex; justify-content:space-between;"><span>Paid</span><strong>{{ number_format((float) $billing->paid_amount, 2) }}</strong></div>
                    <div style="display:flex; justify-content:space-between;"><span>Balance</span><strong>{{ number_format((float) $billing->balance_due, 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
