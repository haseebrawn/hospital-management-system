@props([
    'appointment' => null,
    'patients' => collect(),
    'doctors' => collect(),
    'departments' => collect(),
    'statusOptions' => ['pending', 'approved', 'completed', 'cancelled'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $patientId = old('patient_id', $appointment?->patient_id);
        $doctorId = old('doctor_id', $appointment?->doctor_id);
        $departmentId = old('department_id', $appointment?->department_id);
        $status = old('status', $appointment?->status ?? 'pending');
        $date = old('date', $appointment?->date);
        $time = old('time', $appointment?->time ? substr((string) $appointment->time, 0, 5) : null);
        $reason = old('reason', $appointment?->reason);
        $notes = old('notes', $appointment?->notes);
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Patient</label>
            <select name="patient_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $patientId ? '' : 'selected' }}>Select patient</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" {{ (string) $patientId === (string) $p->id ? 'selected' : '' }}>
                        {{ $p->mrn ?? ('#' . $p->id) }} — {{ $p->first_name }} {{ $p->last_name }} ({{ $p->contact_number }})
                    </option>
                @endforeach
            </select>
            <div style="margin-top:6px; font-size:12px; color: var(--text-muted);">
                Tip: appointments with a selected doctor must match an active doctor availability slot.
            </div>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Doctor (optional)</label>
            <select name="doctor_id"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="">— None —</option>
                @foreach ($doctors as $d)
                    <option value="{{ $d->id }}" {{ (string) $doctorId === (string) $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Department</label>
            <select name="department_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $departmentId ? '' : 'selected' }}>Select department</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Date</label>
            <input type="date" name="date" value="{{ $date }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Time</label>
            <input type="time" name="time" value="{{ $time }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Reason / Complaint</label>
            <input name="reason"
                value="{{ $reason }}"
                placeholder="e.g. Fever, follow-up, chest pain"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Notes</label>
            <textarea name="notes"
                rows="4"
                placeholder="Optional appointment notes for doctor/reception/admin"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $notes }}</textarea>
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Status</label>
            <select name="status" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($statusOptions as $opt)
                    <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $opt)) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('appointments.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
