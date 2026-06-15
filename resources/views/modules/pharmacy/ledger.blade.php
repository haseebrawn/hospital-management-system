@extends('layouts.app')

@section('title', 'Stock Ledger')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Stock Ledger</div>
                <div class="card-subtitle">Track opening stock, manual adjustments, and dispense movements.</div>
            </div>

            <form method="GET" action="{{ route('pharmacy.ledger.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <select name="medicine_id"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                    <option value="">All medicines</option>
                    @foreach ($medicines as $medicine)
                        <option value="{{ $medicine->id }}" {{ (string) $medicineId === (string) $medicine->id ? 'selected' : '' }}>
                            {{ $medicine->name }} ({{ $medicine->stock }})
                        </option>
                    @endforeach
                </select>

                <select name="movement_type"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                    <option value="">All movements</option>
                    @foreach ($movementTypes as $type)
                        <option value="{{ $type }}" {{ ($movementType ?? '') === $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                    Filter
                </button>
            </form>
        </div>

        <div style="margin-top:16px; overflow:auto;">
            <table class="dash-table" style="min-width:1200px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Medicine</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Reference</th>
                        <th>Performed By</th>
                        <th>Notes</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>{{ $movement->id }}</td>
                            <td style="font-weight:700;">{{ $movement->medicine->name ?? '-' }}</td>
                            <td style="text-transform:capitalize;">{{ $movement->movement_type }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td>{{ $movement->stock_before }}</td>
                            <td>{{ $movement->stock_after }}</td>
                            <td>{{ $movement->reference ?? '-' }}</td>
                            <td>{{ $movement->performer->name ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($movement->notes ?: '-', 40) }}</td>
                            <td>{{ optional($movement->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:16px;">No stock movements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $movements->links() }}
        </div>
    </div>
@endsection
