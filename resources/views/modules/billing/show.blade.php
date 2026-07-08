@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="card">
        <div class="page-header">
            <div>
                <div class="card-title">{{ $billing->invoice_number ?? ('Invoice #' . $billing->id) }}</div>
                <div class="card-subtitle">
                    Status: <span style="text-transform:capitalize; font-weight:700;">{{ $billing->status }}</span>
                    · Total: <span style="font-weight:800;">{{ number_format((float) $billing->total_amount, 2) }}</span>
                    · Paid: <span style="font-weight:800;">{{ number_format((float) $billing->paid_amount, 2) }}</span>
                    · Balance: <span style="font-weight:800;">{{ number_format((float) $billing->balance_due, 2) }}</span>
                </div>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('billing.receipt', $billing) }}" class="page-button page-button--soft">
                    Printable Receipt
                </a>
                @if ($billing->balance_due > 0 && $billing->status !== 'cancelled')
                    <form method="POST" action="{{ route('billing.pay', $billing) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            onclick="return confirm('Mark the remaining balance as paid?')"
                            class="page-button" style="border:none; background:#10b981; color:#fff; cursor:pointer; font-weight:700;">
                            Mark Paid
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('billing.destroy', $billing) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this invoice?')"
                        class="page-button" style="border:1px solid rgba(239,68,68,0.35); background: transparent; color:#dc2626; cursor:pointer; font-weight:700;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px;" class="page-grid-2">
            <div class="info-card">
                <div class="info-card__label">Patient</div>
                <div class="info-card__value">
                    {{ optional($billing->patient)->first_name }} {{ optional($billing->patient)->last_name }}
                </div>
                <div class="section-panel__muted" style="margin-top:2px;">
                    {{ optional($billing->patient)->contact_number }}
                </div>
            </div>
            <div class="info-card">
                <div class="info-card__label">Payment Summary</div>
                <div class="info-card__value">
                    Method: {{ $billing->payment_method ?: '-' }}
                </div>
                <div class="section-panel__muted" style="margin-top:2px;">
                    Created By: {{ optional($billing->creator)->name ?? '-' }}
                </div>
                <div class="section-panel__muted" style="margin-top:2px;">
                    Approved By: {{ optional($billing->approver)->name ?? '-' }}
                </div>
            </div>
        </div>

        <div style="margin-top:16px;" class="section-panel">
            <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div class="section-panel__title">Payment Progress</div>
                <div style="font-size:12px; color:var(--text-muted);">{{ $paymentSummary['payment_count'] }} payment(s) recorded</div>
            </div>
            <div style="margin-top:10px; height:10px; border-radius:999px; background:#e5e7eb; overflow:hidden;">
                <div style="width: {{ $paymentSummary['progress'] }}%; height:100%; background: linear-gradient(90deg, var(--primary), #10b981);"></div>
            </div>
            <div style="margin-top:10px; display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px;">
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Paid</div>
                    <div style="font-weight:700;">{{ number_format($paymentSummary['paid_amount'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Due</div>
                    <div style="font-weight:700;">{{ number_format($paymentSummary['balance_due'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Latest Payment</div>
                    <div style="font-weight:700;">{{ $paymentSummary['latest_payment']['method'] ?? '-' }}</div>
                </div>
            </div>
            @if (! empty($paymentSummary['latest_payment']))
                <div style="margin-top:12px; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; background:rgba(37,99,235,0.04);">
                    <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                        <div style="font-weight:700;">Latest payment: {{ number_format($paymentSummary['latest_payment']['amount'], 2) }}</div>
                        <div style="font-size:12px; color:var(--text-muted);">{{ $paymentSummary['latest_payment']['received_at'] }}</div>
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                        Received by {{ $paymentSummary['latest_payment']['received_by'] }}
                        @if ($paymentSummary['latest_payment']['reference'])
                            · Ref: {{ $paymentSummary['latest_payment']['reference'] }}
                        @endif
                    </div>
                    @if ($paymentSummary['latest_payment']['notes'])
                        <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">{{ $paymentSummary['latest_payment']['notes'] }}</div>
                    @endif
                </div>
            @endif
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

        @if (! empty($sourceChain))
            <div style="margin-top:16px; padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-weight:700; margin-bottom:8px;">Source Chain</div>
                <div style="display:grid; gap:10px;">
                    @foreach ($sourceChain as $source)
                        <div style="padding:10px 12px; border:1px solid var(--border-color); border-radius:12px;">
                            <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                                <div style="font-weight:700;">{{ $source['label'] }} {{ $source['reference'] }}</div>
                                <div style="font-size:12px; color:var(--text-muted);">{{ $source['service'] }}</div>
                            </div>
                            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">{{ $source['name'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

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
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:10px;">
                        Recording a partial payment will update the paid amount, balance, and invoice status automatically.
                    </div>
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
