@extends('layouts.app')

@section('title', 'Patient Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">
                    {{ $patient->first_name }} {{ $patient->last_name }}
                </div>
                <div class="card-subtitle">Patient profile</div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                <a href="{{ route('patients.history', $patient) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Medical History
                </a>
                <a href="{{ route('patients.edit', $patient) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this patient?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">MRN / Registration No.</div>
                <div style="font-weight:600; margin-top:4px;">{{ $patient->mrn ?? '-' }}</div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Contact</div>
                <div style="font-weight:600; margin-top:4px;">{{ $patient->contact_number }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Gender</div>
                <div style="font-weight:600; margin-top:4px; text-transform:capitalize;">{{ $patient->gender }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Department</div>
                <div style="font-weight:600; margin-top:4px;">{{ optional($patient->department)->name ?? '-' }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Address</div>
                <div style="font-weight:600; margin-top:4px;">{{ $patient->address ?: '-' }}</div>
            </div>
        </div>

        <div style="margin-top: 16px; padding: 14px; border:1px solid var(--border-color); border-radius:14px;">
            <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div>
                    <div style="font-weight:800;">Care Workflow Preview</div>
                    <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Latest appointment snapshot with the same timeline used in patient history.</div>
                </div>
                <a href="{{ route('patients.history', $patient) }}"
                    style="padding:8px 12px; border-radius:10px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    Open Full History
                </a>
            </div>

            @if ($latestAppointment)
                <div style="margin-top: 14px; display:grid; grid-template-columns: repeat(4, minmax(150px, 1fr)); gap: 12px;">
                    <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                        <div style="font-size:12px; color:var(--text-muted);">Latest Visit</div>
                        <div style="font-weight:700; margin-top:4px;">{{ $latestAppointment->date }} {{ substr((string) $latestAppointment->time, 0, 5) }}</div>
                    </div>
                    <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                        <div style="font-size:12px; color:var(--text-muted);">Doctor</div>
                        <div style="font-weight:700; margin-top:4px;">{{ optional($latestAppointment->doctor)->name ?? '-' }}</div>
                    </div>
                    <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                        <div style="font-size:12px; color:var(--text-muted);">Visit Status</div>
                        <div style="font-weight:700; margin-top:4px; text-transform:capitalize;">{{ $latestAppointment->visit_status }}</div>
                    </div>
                    <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                        <div style="font-size:12px; color:var(--text-muted);">Workflow</div>
                        <div style="font-weight:700; margin-top:4px;">{{ collect($latestAppointment->workflowTimeline ?? [])->where('done', true)->count() }} / 5 completed</div>
                    </div>
                </div>

                <div style="margin-top: 12px; display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach ($latestAppointment->workflowTimeline ?? [] as $step)
                        <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid {{ $step['done'] ? 'rgba(34,197,94,0.28)' : 'rgba(148,163,184,0.28)' }}; background:{{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; color:{{ $step['done'] ? '#166534' : '#64748b' }};">
                            <span style="width:8px; height:8px; border-radius:999px; background:{{ $step['done'] ? '#22c55e' : '#cbd5e1' }};"></span>
                            {{ $step['label'] }}
                            <span style="font-size:11px; opacity:.85;">{{ $step['done'] ? 'Done' : 'Pending' }}</span>
                        </span>
                    @endforeach
                </div>
            @else
                <div style="margin-top: 12px; padding: 12px; border:1px dashed var(--border-color); border-radius:12px; color: var(--text-muted); font-size:13px;">
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
