@extends('layouts.app')

@section('title', 'Billing')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Billing</div>
                <div class="card-subtitle">Create invoices and manage payments.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('billing.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient name / phone"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">

                    <select name="status"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $opt)
                            <option value="{{ $opt }}" {{ ($status ?? '') === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>
                </form>

                <a href="{{ route('billing.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Invoice
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">Invoice #</th>
                        <th class="u-nowrap">ID</th>
                        <th>Patient Name</th>
                        <th class="u-nowrap">Phone</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billings as $inv)
                        <tr>
                            <td class="u-nowrap" style="font-weight:700;">{{ $inv->invoice_number ?? '-' }}</td>
                            <td class="u-nowrap">{{ $inv->id }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('billing.show', $inv) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($inv->patient)->first_name }} {{ optional($inv->patient)->last_name }}
                                </a>
                            </td>
                            <td class="u-nowrap">{{ optional($inv->patient)->contact_number ?? '-' }}</td>
                            <td class="u-nowrap">{{ number_format((float) $inv->total_amount, 2) }}</td>
                            <td class="u-nowrap">{{ number_format((float) $inv->paid_amount, 2) }}</td>
                            <td class="u-nowrap">{{ number_format((float) $inv->balance_due, 2) }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ $inv->status }}</td>
                            <td>{{ optional($inv->creator)->name ?? '-' }}</td>
                            <td class="u-nowrap">{{ optional($inv->created_at)->format('Y-m-d H:i') }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('billing.show', $inv) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    View
                                </a>
                                <form method="POST" action="{{ route('billing.destroy', $inv) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this invoice?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding: 16px;">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $billings->links() }}
        </div>
    </div>
@endsection
