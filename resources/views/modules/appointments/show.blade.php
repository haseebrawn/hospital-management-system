@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Appointment #{{ $appointment->id }}</div>
                <div class="card-subtitle">
                    {{ $appointment->date }} • {{ substr((string) $appointment->time, 0, 5) }} •
                    <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $appointment->status) }}</span>
                </div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                @if ($appointment->canCheckIn())
                    <form method="POST" action="{{ route('appointments.check-in', $appointment) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            style="padding:8px 12px; border-radius:10px; border:1px solid rgba(5,150,105,0.30); background:rgba(5,150,105,0.08); color:#059669; cursor:pointer; font-size:13px; font-weight:700;">
                            Check In
                        </button>
                    </form>
                @elseif ($appointment->canCheckOut())
                    <form method="POST" action="{{ route('appointments.check-out', $appointment) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            style="padding:8px 12px; border-radius:10px; border:1px solid rgba(124,58,237,0.30); background:rgba(124,58,237,0.08); color:#7c3aed; cursor:pointer; font-size:13px; font-weight:700;">
                            Check Out
                        </button>
                    </form>
                @endif
                <a href="{{ route('appointments.edit', $appointment) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('appointments.destroy', $appointment) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this appointment?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Patient</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($appointment->patient)->first_name }} {{ optional($appointment->patient)->last_name }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    {{ optional($appointment->patient)->contact_number }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Doctor</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($appointment->doctor)->name ?? '-' }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Department</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($appointment->department)->name ?? '-' }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Created</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($appointment->created_at)->format('Y-m-d H:i') }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Visit Flow</div>
                <div style="font-weight:600; margin-top:4px; text-transform:capitalize;">
                    {{ str_replace('_', ' ', $appointment->visit_status) }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Check In / Out</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($appointment->checked_in_at)->format('Y-m-d H:i') ?? '-' }}
                    <span style="color:var(--text-muted);">→</span>
                    {{ optional($appointment->checked_out_at)->format('Y-m-d H:i') ?? '-' }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; grid-column: 1 / -1;">
                <div style="font-size:12px; color: var(--text-muted);">Reason / Complaint</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ $appointment->reason ?: '-' }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; grid-column: 1 / -1;">
                <div style="font-size:12px; color: var(--text-muted);">Notes</div>
                <div style="font-weight:600; margin-top:4px; white-space:pre-line;">
                    {{ $appointment->notes ?: '-' }}
                </div>
            </div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('appointments.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to appointments
            </a>
        </div>
    </div>
@endsection
