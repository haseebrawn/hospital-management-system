@extends('layouts.app')

@section('title', 'Patient Details')

@section('content')
    <div class="card">
        <div class="page-header">
            <div>
                <div class="card-title">
                    {{ $patient->first_name }} {{ $patient->last_name }}
                </div>
                <div class="card-subtitle">Patient profile</div>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('patients.history', $patient) }}" class="page-button page-button--neutral">
                    Medical History
                </a>
                <a href="{{ route('patients.edit', $patient) }}" class="page-button page-button--neutral">
                    Edit
                </a>
                <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this patient?')"
                        class="page-button"
                        style="border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-weight:700;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px;" class="page-grid-2">
            <div class="info-card">
                <div class="info-card__label">MRN / Registration No.</div>
                <div class="info-card__value">{{ $patient->mrn ?? '-' }}</div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Contact</div>
                <div class="info-card__value">{{ $patient->contact_number }}</div>
            </div>
            <div class="info-card">
                <div class="info-card__label">Gender</div>
                <div class="info-card__value" style="text-transform:capitalize;">{{ $patient->gender }}</div>
            </div>
            <div class="info-card">
                <div class="info-card__label">Department</div>
                <div class="info-card__value">{{ optional($patient->department)->name ?? '-' }}</div>
            </div>
            <div class="info-card">
                <div class="info-card__label">Address</div>
                <div class="info-card__value">{{ $patient->address ?: '-' }}</div>
            </div>
        </div>

        <div style="margin-top: 16px;" class="section-panel">
            <div class="page-header">
                <div>
                    <div class="section-panel__title">Care Workflow Preview</div>
                    <div class="section-panel__muted" style="margin-top:4px;">Latest appointment snapshot with the same timeline used in patient history.</div>
                </div>
                <a href="{{ route('patients.history', $patient) }}"
                    class="page-button page-button--primary">
                    Open Full History
                </a>
            </div>

            @if ($latestAppointment)
                <div style="margin-top: 14px;" class="page-grid-2">
                    <div class="info-card">
                        <div class="info-card__label">Latest Visit</div>
                        <div class="info-card__value">{{ $latestAppointment->date }} {{ substr((string) $latestAppointment->time, 0, 5) }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-card__label">Doctor</div>
                        <div class="info-card__value">{{ optional($latestAppointment->doctor)->name ?? '-' }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-card__label">Visit Status</div>
                        <div class="info-card__value" style="text-transform:capitalize;">{{ $latestAppointment->visit_status }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-card__label">Workflow</div>
                        <div class="info-card__value">{{ collect($latestAppointment->workflowTimeline ?? [])->where('done', true)->count() }} / 5 completed</div>
                    </div>
                </div>

                <div style="margin-top: 12px; display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach ($latestAppointment->workflowTimeline ?? [] as $step)
                        <span class="workflow-chip"
                            style="padding: 5px 9px; font-size: 12px; --workflow-chip-border: {{ $step['done'] ? 'rgba(34,197,94,0.28)' : 'rgba(148,163,184,0.28)' }}; --workflow-chip-bg: {{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; --workflow-chip-color: {{ $step['done'] ? '#166534' : '#64748b' }}; --workflow-chip-dot: {{ $step['done'] ? '#22c55e' : '#cbd5e1' }};">
                            <span class="workflow-chip__dot" style="width:8px; height:8px;"></span>
                            {{ $step['label'] }}
                            <span style="font-size:11px; opacity:.85;">{{ $step['done'] ? 'Done' : 'Pending' }}</span>
                        </span>
                    @endforeach
                </div>
            @else
                <div style="margin-top: 12px; padding: 12px; border:1px dashed var(--border-color); border-radius:12px; color: var(--text-muted); font-size:13px; background:#fff;">
                    No appointment history yet. Open patient history to start the clinical workflow.
                </div>
            @endif
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('patients.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to patients
            </a>
        </div>
    </div>
@endsection
