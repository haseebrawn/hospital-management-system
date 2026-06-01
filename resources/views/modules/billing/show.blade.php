@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Invoice #{{ $billing->id }}</div>
                <div class="card-subtitle">
                    Status: <span style="text-transform:capitalize; font-weight:700;">{{ $billing->status }}</span>
                    • Total: <span style="font-weight:800;">{{ number_format((float) $billing->total_amount, 2) }}</span>
                </div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                @if ($billing->status === 'pending')
                    <form method="POST" action="{{ route('billing.pay', $billing) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            onclick="return confirm('Mark this invoice as paid?')"
                            style="padding:8px 12px; border-radius:10px; border:none; background:#10b981; color:#fff; cursor:pointer; font-size:13px;">
                            Mark Paid
                        </button>
                    </form>
                    <form method="POST" action="{{ route('billing.cancel', $billing) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            onclick="return confirm('Cancel this invoice?')"
                            style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                            Cancel
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('billing.destroy', $billing) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this invoice?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: transparent; color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Patient</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($billing->patient)->first_name }} {{ optional($billing->patient)->last_name }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    {{ optional($billing->patient)->contact_number }}
                </div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Created</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($billing->created_at)->format('Y-m-d H:i') }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    By: {{ optional($billing->creator)->name ?? '-' }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    Approved: {{ optional($billing->approver)->name ?? '-' }}
                </div>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($billing->items as $item)
                        <tr>
                            <td style="font-weight:600;">{{ $item->service_name }}</td>
                            <td style="text-transform:capitalize;">{{ $item->type }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format((float) $item->price, 2) }}</td>
                            <td style="font-weight:700;">
                                {{ number_format((float) $item->price * (int) $item->quantity, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top: 10px; font-size:14px;">
            <div style="font-weight:900;">
                Total: {{ number_format((float) $billing->total_amount, 2) }}
            </div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('billing.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to invoices
            </a>
        </div>
    </div>
@endsection

