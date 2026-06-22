@props([
    'medicalRecord' => null,
    'linkedAppointment' => null,
    'patients' => collect(),
    'doctors' => collect(),
    'appointments' => collect(),
    'visitTypes' => ['consultation', 'follow_up', 'emergency', 'admission', 'discharge'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $patientId = old('patient_id', $medicalRecord?->patient_id);
        $doctorId = old('doctor_id', $medicalRecord?->doctor_id);
        $appointmentId = old('appointment_id', $medicalRecord?->appointment_id);
        $visitType = old('visit_type', $medicalRecord?->visit_type ?? 'consultation');
    @endphp

    @if ($linkedAppointment)
        <div style="margin-bottom:14px; padding:14px; border:1px solid rgba(37,99,235,0.18); border-radius:14px; background:rgba(37,99,235,0.05);">
            <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">Linked Appointment Context</div>
            <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px;">
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Appointment</div>
                    <div style="font-weight:700;">#{{ $linkedAppointment->id }} — {{ $linkedAppointment->date }} {{ substr((string) $linkedAppointment->time, 0, 5) }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Patient</div>
                    <div style="font-weight:700;">{{ optional($linkedAppointment->patient)->mrn ?? 'No MRN' }} — {{ optional($linkedAppointment->patient)->first_name }} {{ optional($linkedAppointment->patient)->last_name }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Doctor</div>
                    <div style="font-weight:700;">{{ optional($linkedAppointment->doctor)->name ?? 'No linked doctor' }}</div>
                </div>
            </div>
            <div style="margin-top:12px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Reason</div>
                    <div style="font-weight:600;">{{ $linkedAppointment->reason ?: '-' }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-muted);">Notes</div>
                    <div style="font-weight:600;">{{ $linkedAppointment->notes ?: '-' }}</div>
                </div>
            </div>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div>
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Patient</label>
            <select name="patient_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $patientId ? '' : 'selected' }}>Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ (string) $patientId === (string) $patient->id ? 'selected' : '' }}>
                        {{ $patient->mrn ?? 'No MRN' }} — {{ $patient->first_name }} {{ $patient->last_name }}
                    </option>
                @endforeach
            </select>
            <div style="margin-top:6px; font-size:12px; color:var(--text-muted);">
                Pick the appointment to keep the record linked to the visit.
            </div>
        </div>

        <div>
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Appointment</label>
            <select name="appointment_id"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="">No appointment link</option>
                @foreach ($appointments as $appointment)
                    <option value="{{ $appointment->id }}" {{ (string) $appointmentId === (string) $appointment->id ? 'selected' : '' }}>
                        #{{ $appointment->id }} — {{ optional($appointment->patient)->mrn ?? 'No MRN' }}
                        {{ optional($appointment->patient)->first_name }} {{ optional($appointment->patient)->last_name }}
                        ({{ $appointment->date }} {{ substr((string) $appointment->time, 0, 5) }})
                    </option>
                @endforeach
            </select>
            <div style="margin-top:6px; font-size:12px; color:var(--text-muted);">
                The selected appointment should match the chosen patient.
            </div>
        </div>

        @if (! auth()->user()->hasRole('doctor'))
            <div>
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Doctor</label>
                <select name="doctor_id"
                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                    <option value="">Use appointment doctor / no doctor</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Visit Type</label>
            <select name="visit_type" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($visitTypes as $type)
                    <option value="{{ $type }}" {{ $visitType === $type ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>
            <div style="margin-top:6px; font-size:12px; color:var(--text-muted);">
                Uses the appointment doctor when available.
            </div>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Chief Complaint</label>
            <input name="chief_complaint" value="{{ old('chief_complaint', $medicalRecord?->chief_complaint) }}" placeholder="Main patient complaint"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Diagnosis</label>
            <textarea name="diagnosis" rows="4" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ old('diagnosis', $medicalRecord?->diagnosis) }}</textarea>
        </div>

        <div>
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Vitals</label>
            <textarea name="vitals" rows="4" placeholder="BP, pulse, temperature, weight..."
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ old('vitals', $medicalRecord?->vitals) }}</textarea>
        </div>

        <div>
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Allergies</label>
            <textarea name="allergies" rows="4" placeholder="Known allergies or none"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ old('allergies', $medicalRecord?->allergies) }}</textarea>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Medical History</label>
            <textarea name="history" rows="4"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ old('history', $medicalRecord?->history) }}</textarea>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Doctor Notes</label>
            <textarea name="notes" rows="4"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ old('notes', $medicalRecord?->notes) }}</textarea>
        </div>

        <div>
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Follow-up Date</label>
            <input type="date" name="follow_up_date" value="{{ old('follow_up_date', optional($medicalRecord?->follow_up_date)->format('Y-m-d')) }}"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
        <a href="{{ route('medical-records.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background:var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
