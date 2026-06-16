@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">{{ $billing->invoice_number ?? ('Invoice #' . $billing->id) }}</div>
                <div class="card-subtitle">
                    Status: <span style="text-transform:capitalize; font-weight:700;">{{ $billing->status }}</span>
                    · Total: <span style="font-weight:800;">{{ number_format((float) $billing->total_amount, 2) }}</span>
                    · Paid: <span style="font-weight:800;">{{ number_format((float) $billing->paid_amount, 2) }}</span>
                    · Balance: <span style="font-weight:800;">{{ number_format((float) $billing->balance_due, 2) }}</span>
                </div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                <a href="{{ route('billing.receipt', $billing) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid rgba(37,99,235,0.30); background:rgba(37,99,235,0.08); color:#2563eb; text-decoration:none; font-size:13px; font-weight:700;">
                    Printable Receipt
                </a>
                @if ($billing->balance_due > 0 && $billing->status !== 'cancelled')
                    <form method="POST" action="{{ route('billing.pay', $billing) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            onclick="return confirm('Mark the remaining balance as paid?')"
                            style="padding:8px 12px; border-radius:10px; border:none; background:#10b981; color:#fff; cursor:pointer; font-size:13px;">
                            Mark Paid
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
                <div style="font-size:12px; color: var(--text-muted);">Payment Summary</div>
                <div style="font-weight:600; margin-top:4px;">
                    Method: {{ $billing->payment_method ?: '-' }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    Created By: {{ optional($billing->creator)->name ?? '-' }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    Approved By: {{ optional($billing->approver)->name ?? '-' }}
                </div>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Line total</th>
                        <th>Source</th>
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
                            <td>
                                <div style="font-weight:700;">{{ $item->source_type ?: '-' }}</div>
                                <div style="font-size:12px; color:var(--text-muted);">
                                    #{{ $item->source_id ?: '-' }} {{ $item->source_name ? '· ' . $item->source_name : '' }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px; align-items:start;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-weight:700; margin-bottom:8px;">Payments</div>
                <div style="display:grid; gap:10px;">
                    @forelse ($billing->payments as $payment)
                        <div style="padding:10px 12px; border:1px solid var(--border-color); border-radius:12px;">
                            <div style="display:flex; justify-content:space-between; gap:12px;">
                                <div style="font-weight:700;">{{ number_format((float) $payment->amount, 2) }}</div>
                                <div style="text-transform:capitalize;">{{ $payment->payment_method }}</div>
                            </div>
                            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                                By {{ optional($payment->receiver)->name ?? '-' }} · {{ optional($payment->created_at)->format('Y-m-d H:i') }}
                            </div>
                            @if ($payment->reference)
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Ref: {{ $payment->reference }}</div>
                            @endif
                            @if ($payment->notes)
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ $payment->notes }}</div>
                            @endif
                        </div>
                    @empty
                        <div style="font-size:13px; color: var(--text-muted);">No payments recorded yet.</div>
                    @endforelse
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-weight:700; margin-bottom:8px;">Record Payment</div>
                @if ($billing->status !== 'cancelled' && $billing->balance_due > 0)
                    <form method="POST" action="{{ route('billing.payments.store', $billing) }}">
                        @csrf
                        <div style="display:grid; gap:10px;">
                            <div>
                                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Amount</label>
                                <input type="number" min="0.01" step="0.01" name="amount" value="{{ old('amount', $billing->balance_due) }}" required
                                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Payment Method</label>
                                <select name="payment_method" required
                                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; background:#fff;">
                                    @foreach (['cash', 'card', 'bank_transfer', 'online', 'insurance'] as $method)
                                        <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Reference</label>
                                <input name="reference" placeholder="Optional reference"
                                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Notes</label>
                                <textarea name="notes" rows="3" style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; resize:vertical;"></textarea>
                            </div>
                            <button type="submit"
                                style="padding:10px 14px; border-radius:12px; border:none; background:var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                                Save Payment
                            </button>
                        </div>
                    </form>
                @else
                    <div style="font-size:13px; color:var(--text-muted);">No payment can be recorded for this invoice.</div>
                @endif
            </div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('billing.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                <- Back to invoices
            </a>
        </div>
    </div>
@endsection
