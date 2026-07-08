@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
    <div class="card">
        <div class="page-header">
            <div>
                <div class="card-title">Appointment #{{ $appointment->id }}</div>
                <div class="card-subtitle">
                    {{ $appointment->date }} • {{ substr((string) $appointment->time, 0, 5) }} •
                    <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $appointment->status) }}</span>
                </div>
            </div>
            <div class="page-header__actions">
                @if ($appointment->patient)
                    <a href="{{ route('patients.history', $appointment->patient) }}" class="page-button page-button--soft">
                        Patient History
                    </a>
                @endif
                @if ($appointment->canCheckIn())
                    <form method="POST" action="{{ route('appointments.check-in', $appointment) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="page-button" style="border:1px solid rgba(5,150,105,0.30); background:rgba(5,150,105,0.08); color:#059669; cursor:pointer; font-weight:700;">
                            Check In
                        </button>
                    </form>
                @elseif ($appointment->canCheckOut())
                    <form method="POST" action="{{ route('appointments.check-out', $appointment) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="page-button" style="border:1px solid rgba(124,58,237,0.30); background:rgba(124,58,237,0.08); color:#7c3aed; cursor:pointer; font-weight:700;">
                            Check Out
                        </button>
                    </form>
                @endif
                @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'doctor']))
                    <a href="{{ route('medical-records.create', ['appointment_id' => $appointment->id]) }}" class="page-button page-button--soft">
                        Add Medical Record
                    </a>
                    <a href="{{ route('prescriptions.create', ['appointment_id' => $appointment->id]) }}" class="page-button page-button--soft">
                        Add Prescription
                    </a>
                @endif
                @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'doctor', 'lab_technician']))
                    <a href="{{ route('lab-tests.create', ['appointment_id' => $appointment->id]) }}" class="page-button page-button--soft">
                        Request Lab Test
                    </a>
                @endif
                <a href="{{ route('appointments.edit', $appointment) }}" class="page-button page-button--neutral">
                    Edit
                </a>
                <form method="POST" action="{{ route('appointments.destroy', $appointment) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this appointment?')" class="page-button" style="border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-weight:700;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px;" class="page-grid-2">
            <div class="section-panel" style="grid-column:1 / -1; background:rgba(37,99,235,0.04);">
                <div class="section-panel__title">Care Workflow</div>
                <div style="font-weight:700; margin-top:6px;">Use patient history first, then continue with the medical record and prescription for a complete visit flow.</div>
                <div class="page-header__actions" style="margin-top:12px;">
                    @if ($appointment->patient)
                        <a href="{{ route('patients.history', $appointment->patient) }}" class="page-button page-button--neutral">
                            Open Patient History
                        </a>
                    @endif
                    @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'doctor']))
                        <a href="{{ route('medical-records.create', ['appointment_id' => $appointment->id]) }}" class="page-button page-button--neutral">
                            Start Medical Record
                        </a>
                        <a href="{{ route('prescriptions.create', ['appointment_id' => $appointment->id]) }}" class="page-button page-button--neutral">
                            Start Prescription
                        </a>
                    @endif
                </div>
            </div>

            <div class="section-panel" style="grid-column:1 / -1;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div class="info-card__label">Workflow Timeline</div>
                    <div style="font-size:12px; color:var(--text-muted);">Check-in → Record → Prescription → Lab → Billing</div>
                </div>
                <div style="margin-top:12px; display:grid; gap:10px;">
                    @foreach ($workflowTimeline as $step)
                        <div style="display:grid; grid-template-columns: 24px 1fr; gap:12px; align-items:start;">
                            <div style="width:24px; height:24px; border-radius:999px; display:grid; place-items:center; font-size:12px; font-weight:800; color:{{ $step['done'] ? '#059669' : '#94a3b8' }}; background:{{ $step['done'] ? 'rgba(5,150,105,0.12)' : 'rgba(148,163,184,0.12)' }};">
                                {{ $step['done'] ? '✓' : '•' }}
                            </div>
                            <div style="padding:10px 12px; border:1px solid {{ $step['done'] ? 'rgba(5,150,105,0.25)' : 'var(--border-color)' }}; border-radius:12px; background:{{ $step['done'] ? 'rgba(5,150,105,0.04)' : '#fff' }};">
                                <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                                    <div style="font-weight:700;">{{ $step['label'] }}</div>
                                    <div style="font-size:12px; color:{{ $step['done'] ? '#059669' : 'var(--text-muted)' }}; text-transform:uppercase;">
                                        {{ $step['done'] ? 'Done' : 'Pending' }}
                                    </div>
                                </div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">{{ $step['subtitle'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Patient</div>
                <div class="info-card__value">
                    {{ optional($appointment->patient)->first_name }} {{ optional($appointment->patient)->last_name }}
                </div>
                <div class="section-panel__muted" style="margin-top:2px;">
                    {{ optional($appointment->patient)->contact_number }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Doctor</div>
                <div class="info-card__value">
                    {{ optional($appointment->doctor)->name ?? '-' }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Department</div>
                <div class="info-card__value">
                    {{ optional($appointment->department)->name ?? '-' }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Created</div>
                <div class="info-card__value">
                    {{ optional($appointment->created_at)->format('Y-m-d H:i') }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Visit Flow</div>
                <div class="info-card__value" style="text-transform:capitalize;">
                    {{ str_replace('_', ' ', $appointment->visit_status) }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Check In / Out</div>
                <div class="info-card__value">
                    {{ optional($appointment->checked_in_at)->format('Y-m-d H:i') ?? '-' }}
                    <span style="color:var(--text-muted);">→</span>
                    {{ optional($appointment->checked_out_at)->format('Y-m-d H:i') ?? '-' }}
                </div>
            </div>

            <div class="info-card" style="grid-column: 1 / -1;">
                <div class="info-card__label">Reason / Complaint</div>
                <div class="info-card__value">
                    {{ $appointment->reason ?: '-' }}
                </div>
            </div>

            <div class="info-card" style="grid-column: 1 / -1;">
                <div class="info-card__label">Notes</div>
                <div class="info-card__value" style="white-space:pre-line;">
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
