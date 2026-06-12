@extends('layouts.app')

@section('title', 'Medical Record Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Medical Record #{{ $medicalRecord->id }}</div>
                <div class="card-subtitle">
                    {{ optional($medicalRecord->created_at)->format('Y-m-d H:i') }} —
                    {{ ucfirst(str_replace('_', ' ', $medicalRecord->visit_type)) }}
                </div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('prescriptions.create', ['appointment_id' => $medicalRecord->appointment_id]) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid rgba(124,58,237,0.30); background:rgba(124,58,237,0.08); color:#7c3aed; text-decoration:none; font-size:13px; font-weight:700;">
                    Create Prescription
                </a>
                <a href="{{ route('lab-tests.create', ['appointment_id' => $medicalRecord->appointment_id]) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid rgba(5,150,105,0.30); background:rgba(5,150,105,0.08); color:#059669; text-decoration:none; font-size:13px; font-weight:700;">
                    Request Lab Test
                </a>
                <a href="{{ route('medical-records.edit', $medicalRecord) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('medical-records.destroy', $medicalRecord) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this medical record?')"
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
                    {{ optional($medicalRecord->patient)->mrn ?? '-' }} —
                    {{ optional($medicalRecord->patient)->first_name }} {{ optional($medicalRecord->patient)->last_name }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Doctor</div>
                <div style="font-weight:700; margin-top:4px;">{{ optional($medicalRecord->doctor)->name ?? '-' }}</div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Appointment</div>
                <div style="font-weight:700; margin-top:4px;">
                    @if ($medicalRecord->appointment)
                        #{{ $medicalRecord->appointment_id }} — {{ $medicalRecord->appointment->date }}
                        {{ substr((string) $medicalRecord->appointment->time, 0, 5) }}
                    @else
                        -
                    @endif
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color:var(--text-muted);">Follow-up Date</div>
                <div style="font-weight:700; margin-top:4px;">{{ optional($medicalRecord->follow_up_date)->format('Y-m-d') ?? '-' }}</div>
            </div>

            @foreach ([
                'Chief Complaint' => $medicalRecord->chief_complaint,
                'Diagnosis' => $medicalRecord->diagnosis,
                'Vitals' => $medicalRecord->vitals,
                'Medical History' => $medicalRecord->history,
                'Allergies' => $medicalRecord->allergies,
                'Doctor Notes' => $medicalRecord->notes,
            ] as $label => $value)
                <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; grid-column:1 / -1;">
                    <div style="font-size:12px; color:var(--text-muted);">{{ $label }}</div>
                    <div style="font-weight:600; margin-top:4px; white-space:pre-line;">{{ $value ?: '-' }}</div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:16px;">
            <a href="{{ route('medical-records.index') }}" style="font-size:13px; color:var(--primary); text-decoration:none;">
                ← Back to medical records
            </a>
        </div>
    </div>
@endsection
