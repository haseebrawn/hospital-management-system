@extends('layouts.app')

@section('title', 'Prescription Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Prescription #{{ $prescription->id }}</div>
                <div class="card-subtitle">
                    {{ optional($prescription->created_at)->format('Y-m-d H:i') }} —
                    <span style="text-transform:capitalize;">{{ $prescription->status }}</span>
                </div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('prescriptions.edit', $prescription) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('prescriptions.destroy', $prescription) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this prescription?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background:rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top:14px; display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Patient</div>
                <div style="font-weight:700; margin-top:4px;">
                    {{ optional($prescription->patient)->mrn ?? '-' }} —
                    {{ optional($prescription->patient)->first_name }} {{ optional($prescription->patient)->last_name }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Doctor</div>
                <div style="font-weight:700; margin-top:4px;">{{ optional($prescription->doctor)->name ?? '-' }}</div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Appointment</div>
                <div style="font-weight:700; margin-top:4px;">
                    #{{ $prescription->appointment_id }} —
                    {{ optional($prescription->appointment)->date }}
                    {{ substr((string) optional($prescription->appointment)->time, 0, 5) }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Status</div>
                <div style="font-weight:700; margin-top:4px; text-transform:capitalize;">{{ $prescription->status }}</div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; grid-column:1 / -1;">
                <div style="font-size:12px; color:var(--text-muted);">Diagnosis / Instructions</div>
                <div style="font-weight:600; margin-top:4px; white-space:pre-line;">{{ $prescription->description }}</div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; grid-column:1 / -1;">
                <div style="font-size:12px; color:var(--text-muted);">Prescription Items</div>
                @if ($prescription->items->isNotEmpty())
                    <div style="margin-top:8px; overflow:auto;">
                        <table class="dash-table" style="min-width:760px;">
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Duration</th>
                                    <th>Qty</th>
                                    <th>Instructions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prescription->items as $item)
                                    <tr>
                                        <td style="font-weight:700;">{{ $item->medicine_name }}</td>
                                        <td>{{ $item->dosage ?: '-' }}</td>
                                        <td>{{ $item->frequency ?: '-' }}</td>
                                        <td>{{ $item->duration ?: '-' }}</td>
                                        <td>{{ $item->quantity ?: '-' }}</td>
                                        <td>{{ $item->instructions ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="font-weight:600; margin-top:4px;">No structured items added.</div>
                @endif
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; grid-column:1 / -1;">
                <div style="font-size:12px; color:var(--text-muted);">Legacy Medicines Note</div>
                <div style="font-weight:600; margin-top:4px; white-space:pre-line;">{{ $prescription->medicines ?: '-' }}</div>
            </div>
        </div>

        <div style="margin-top:16px;">
            <a href="{{ route('prescriptions.index') }}" style="font-size:13px; color:var(--primary); text-decoration:none;">
                ← Back to prescriptions
            </a>
        </div>
    </div>
@endsection
