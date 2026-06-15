@extends('layouts.app')

@section('title', 'Dispense Queue')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Dispense Queue</div>
                <div class="card-subtitle">Pending prescriptions ready for pharmacy stock deduction.</div>
            </div>

            <form method="GET" action="{{ route('pharmacy.dispense.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient / prescription"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">
                <button type="submit"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                    Search
                </button>
            </form>
        </div>

        <div style="margin-top:16px; display:grid; grid-template-columns: 1.3fr 0.7fr; gap:16px; align-items:start;">
            <div style="overflow:auto;">
                <table class="dash-table" style="min-width:1080px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($prescriptions as $prescription)
                            <tr>
                                <td>{{ $prescription->id }}</td>
                                <td style="font-weight:700;">
                                    <a href="{{ route('prescriptions.show', $prescription) }}" style="color:inherit; text-decoration:none;">
                                        {{ optional($prescription->patient)->mrn ?? '-' }} —
                                        {{ optional($prescription->patient)->first_name }} {{ optional($prescription->patient)->last_name }}
                                    </a>
                                </td>
                                <td>{{ optional($prescription->doctor)->name ?? '-' }}</td>
                                <td>
                                    {{ $prescription->items->pluck('medicine_name')->join(', ') ?: '-' }}
                                </td>
                                <td style="text-transform:capitalize;">{{ $prescription->status }}</td>
                                <td style="text-align:right;">
                                    <form method="POST" action="{{ route('pharmacy.dispense.store', $prescription) }}">
                                        @csrf
                                        <button type="submit"
                                            onclick="return confirm('Dispense this prescription and deduct stock?')"
                                            style="font-size:13px; color:#059669; background:transparent; border:none; cursor:pointer; font-weight:700;">
                                            Dispense
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding:16px;">No pending prescriptions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top:14px;">
                    {{ $prescriptions->links() }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div class="card-title" style="font-size:18px;">Low Stock Medicines</div>
                <div class="card-subtitle">Stock at or below 10 units.</div>
                <div style="margin-top:12px; display:grid; gap:10px;">
                    @forelse ($lowStockMedicines as $medicine)
                        <div style="padding:10px 12px; border:1px solid var(--border-color); border-radius:12px;">
                            <div style="font-weight:700;">{{ $medicine->name }}</div>
                            <div style="font-size:12px; color:var(--text-muted);">Stock: {{ $medicine->stock }}</div>
                        </div>
                    @empty
                        <div style="font-size:13px; color:var(--text-muted);">No low stock medicines.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
