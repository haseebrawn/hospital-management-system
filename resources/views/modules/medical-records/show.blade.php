@extends('layouts.app')

@section('title', 'Medical Record Details')

@section('content')
    <div class="card">
        <div class="page-header">
            <div>
                <div class="card-title">Medical Record #{{ $medicalRecord->id }}</div>
                <div class="card-subtitle">
                    {{ optional($medicalRecord->created_at)->format('Y-m-d H:i') }} —
                    {{ ucfirst(str_replace('_', ' ', $medicalRecord->visit_type)) }}
                </div>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('prescriptions.create', ['appointment_id' => $medicalRecord->appointment_id]) }}"
                    class="page-button page-button--soft">
                    Create Prescription
                </a>
                <a href="{{ route('lab-tests.create', ['appointment_id' => $medicalRecord->appointment_id]) }}"
                    class="page-button page-button--soft">
                    Request Lab Test
                </a>
                <a href="{{ route('medical-records.edit', $medicalRecord) }}"
                    class="page-button page-button--neutral">
                    Edit
                </a>
                <form method="POST" action="{{ route('medical-records.destroy', $medicalRecord) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this medical record?')"
                        class="page-button"
                        style="border:1px solid rgba(239,68,68,0.35); background:rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-weight:700;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top:14px;" class="page-grid-2">
            <div class="info-card">
                <div class="info-card__label">Patient</div>
                <div class="info-card__value">
                    {{ optional($medicalRecord->patient)->mrn ?? '-' }} —
                    {{ optional($medicalRecord->patient)->first_name }} {{ optional($medicalRecord->patient)->last_name }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Doctor</div>
                <div class="info-card__value">{{ optional($medicalRecord->doctor)->name ?? '-' }}</div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Appointment</div>
                <div class="info-card__value">
                    @if ($medicalRecord->appointment)
                        #{{ $medicalRecord->appointment_id }} — {{ $medicalRecord->appointment->date }}
                        {{ substr((string) $medicalRecord->appointment->time, 0, 5) }}
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Follow-up Date</div>
                <div class="info-card__value">{{ optional($medicalRecord->follow_up_date)->format('Y-m-d') ?? '-' }}</div>
            </div>

            @foreach ([
                'Chief Complaint' => $medicalRecord->chief_complaint,
                'Diagnosis' => $medicalRecord->diagnosis,
                'Vitals' => $medicalRecord->vitals,
                'Medical History' => $medicalRecord->history,
                'Allergies' => $medicalRecord->allergies,
                'Doctor Notes' => $medicalRecord->notes,
            ] as $label => $value)
                <div class="info-card" style="grid-column:1 / -1;">
                    <div class="info-card__label">{{ $label }}</div>
                    <div class="info-card__value" style="white-space:pre-line;">{{ $value ?: '-' }}</div>
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
