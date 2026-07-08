@extends('layouts.app')

@section('title', 'Billing')

@section('content')
    <div class="card">
        <div class="page-header">
            <div>
                <div class="card-title">Billing</div>
                <div class="card-subtitle">Create invoices and manage payments.</div>
            </div>

            <div class="page-header__actions">
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

                    <button type="submit" class="page-button page-button--neutral">Filter</button>
                </form>

                <a href="{{ route('billing.create') }}" class="page-button page-button--primary">+ New Invoice</a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1400px;">
                <thead>
                    <tr>
                        <th class="u-nowrap table-col-name">Invoice #</th>
                        <th class="u-nowrap table-col-id">ID</th>
                        <th class="table-col-name">Patient Name</th>
                        <th class="u-nowrap">Phone</th>
                        <th class="table-col-money">Total</th>
                        <th class="table-col-money">Paid</th>
                        <th class="table-col-money">Balance</th>
                        <th class="table-col-status">Status</th>
                        <th class="table-col-name">Created By</th>
                        <th class="table-col-date">Created</th>
                        <th class="table-col-workflow">Workflow</th>
                        <th class="table-col-actions" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billings as $inv)
                        <tr>
                            <td class="u-nowrap table-col-name" style="font-weight:700;">{{ $inv->invoice_number ?? '-' }}</td>
                            <td class="u-nowrap table-col-id">{{ $inv->id }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('billing.show', $inv) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($inv->patient)->first_name }} {{ optional($inv->patient)->last_name }}
                                </a>
                            </td>
                            <td class="u-nowrap">{{ optional($inv->patient)->contact_number ?? '-' }}</td>
                            <td class="u-nowrap table-col-money">{{ number_format((float) $inv->total_amount, 2) }}</td>
                            <td class="u-nowrap table-col-money">{{ number_format((float) $inv->paid_amount, 2) }}</td>
                            <td class="u-nowrap table-col-money">{{ number_format((float) $inv->balance_due, 2) }}</td>
                            <td class="u-nowrap table-col-status" style="text-transform:capitalize;">{{ $inv->status }}</td>
                            <td>{{ optional($inv->creator)->name ?? '-' }}</td>
                            <td class="u-nowrap table-col-date">{{ optional($inv->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="workflow-chip-row" style="max-width: 420px;">
                                    @foreach ($inv->workflowTimeline ?? [] as $step)
                                        <span class="workflow-chip"
                                            style="--workflow-chip-border: {{ $step['done'] ? 'rgba(34,197,94,0.24)' : 'rgba(148,163,184,0.24)' }}; --workflow-chip-bg: {{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; --workflow-chip-color: {{ $step['done'] ? '#166534' : '#64748b' }}; --workflow-chip-dot: {{ $step['done'] ? '#22c55e' : '#cbd5e1' }};">
                                            <span class="workflow-chip__dot"></span>
                                            {{ $step['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="table-col-actions" style="text-align:right;">
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
                            <td colspan="12" style="padding: 16px;">No invoices found.</td>
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
