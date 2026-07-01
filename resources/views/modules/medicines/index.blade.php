@extends('layouts.app')

@section('title', 'Medicines')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Medicines</div>
                <div class="card-subtitle">Manage pharmacy inventory.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('medicines.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search medicine name"
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

                    @if (!empty($search) || !empty($status))
                        <a href="{{ route('medicines.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('medicines.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Medicine
                </a>
            </div>
        </div>

        <div style="margin-top:16px; padding:12px; border:1px solid var(--border-color); border-radius:14px; background:linear-gradient(135deg, rgba(245,158,11,0.08), rgba(239,68,68,0.06));">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                <div>
                    <div style="font-weight:700;">Expiry and Reorder Alerts</div>
                    <div style="font-size:12px; color:var(--text-muted);">
                        Medicines expiring within 30 days or at/below reorder level.
                    </div>
                </div>
                <div style="font-size:13px; font-weight:700;">{{ $alerts->count() }} alert(s)</div>
            </div>

            <div style="margin-top:10px; display:grid; gap:10px;">
                @forelse ($alerts as $alert)
                    <div style="display:flex; justify-content:space-between; gap:12px; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; background:#fff;">
                        <div>
                            <div style="font-weight:700;">{{ $alert['name'] }}</div>
                            <div style="font-size:12px; color:var(--text-muted);">
                                Stock {{ $alert['stock'] }} / Reorder {{ $alert['reorder_level'] }}
                                @if ($alert['expiry_date'])
                                    | Expiry {{ $alert['expiry_date'] }}
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            @if ($alert['is_expired'])
                                <span style="padding:5px 10px; border-radius:999px; background:rgba(239,68,68,0.12); color:#dc2626; font-size:12px; font-weight:700;">Expired</span>
                            @elseif ($alert['is_expiring'])
                                <span style="padding:5px 10px; border-radius:999px; background:rgba(245,158,11,0.14); color:#b45309; font-size:12px; font-weight:700;">Expiring Soon</span>
                            @endif
                            @if ($alert['is_low_stock'])
                                <span style="padding:5px 10px; border-radius:999px; background:rgba(37,99,235,0.12); color:#2563eb; font-size:12px; font-weight:700;">Low Stock</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="font-size:13px; color:var(--text-muted);">No active expiry or reorder alerts.</div>
                @endforelse
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1260px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Name</th>
                        <th>Stock</th>
                        <th>Reorder</th>
                        <th>Price</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Preview</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($medicines as $med)
                        <tr>
                            <td class="u-nowrap">{{ $med->id }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('medicines.show', $med) }}" style="color:inherit; text-decoration:none;">
                                    {{ $med->name }}
                                </a>
                            </td>
                            <td class="u-nowrap">{{ $med->stock }}</td>
                            <td class="u-nowrap">{{ $med->reorder_level ?? 10 }}</td>
                            <td class="u-nowrap">{{ number_format((float) $med->price, 2) }}</td>
                            <td class="u-nowrap">{{ $med->expiry_date ?? '-' }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ $med->status }}</td>
                            <td>
                                        <div class="workflow-chip-row" style="max-width: 420px;">
                                            @foreach ($med->statusPreview ?? [] as $step)
                                                <span class="workflow-chip"
                                                    style="--workflow-chip-border: {{ $step['done'] ? 'rgba(34,197,94,0.24)' : 'rgba(148,163,184,0.24)' }}; --workflow-chip-bg: {{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; --workflow-chip-color: {{ $step['done'] ? '#166534' : '#64748b' }}; --workflow-chip-dot: {{ $step['done'] ? '#22c55e' : '#cbd5e1' }};">
                                                    <span class="workflow-chip__dot"></span>
                                                    {{ $step['label'] }}: {{ $step['value'] }}
                                                </span>
                                            @endforeach
                                        </div>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('medicines.edit', $med) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('medicines.destroy', $med) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this medicine?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 16px;">No medicines found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $medicines->links() }}
        </div>
    </div>
@endsection
