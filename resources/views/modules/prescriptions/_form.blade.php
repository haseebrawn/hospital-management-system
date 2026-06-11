@props([
    'prescription' => null,
    'appointments' => collect(),
    'doctors' => collect(),
    'statusOptions' => ['pending', 'dispensed', 'cancelled'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $appointmentId = old('appointment_id', $prescription?->appointment_id);
        $doctorId = old('doctor_id', $prescription?->doctor_id);
        $description = old('description', $prescription?->description);
        $medicines = old('medicines', $prescription?->medicines);
        $status = old('status', $prescription?->status ?? 'pending');
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Appointment</label>
            <select name="appointment_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $appointmentId ? '' : 'selected' }}>Select appointment</option>
                @foreach ($appointments as $appointment)
                    <option value="{{ $appointment->id }}" {{ (string) $appointmentId === (string) $appointment->id ? 'selected' : '' }}>
                        #{{ $appointment->id }} — {{ optional($appointment->patient)->mrn ?? 'No MRN' }} —
                        {{ optional($appointment->patient)->first_name }} {{ optional($appointment->patient)->last_name }}
                        ({{ $appointment->date }} {{ substr((string) $appointment->time, 0, 5) }})
                    </option>
                @endforeach
            </select>
        </div>

        @if (! auth()->user()->hasRole('doctor'))
            <div style="grid-column:1 / -1;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Doctor</label>
                <select name="doctor_id"
                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                    <option value="">Use appointment doctor</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Diagnosis / Instructions</label>
            <textarea name="description" rows="5" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $description }}</textarea>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Medicines</label>
            <textarea name="medicines" rows="4" placeholder="Example: Paracetamol 500mg - twice daily - 3 days"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $medicines }}</textarea>
            <div style="margin-top:6px; font-size:12px; color:var(--text-muted);">
                Structured medicine items will be added in the next Phase C step.
            </div>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Status</label>
            <select name="status" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($statusOptions as $option)
                    <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
        <a href="{{ route('prescriptions.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background:var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
