@props([
    'labTest' => null,
    'patients' => collect(),
    'doctors' => collect(),
    'technicians' => collect(),
    'statusOptions' => ['pending', 'in_process', 'completed'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $patientId = old('patient_id', $labTest?->patient_id);
        $doctorId = old('doctor_id', $labTest?->doctor_id);
        $technicianId = old('lab_technician_id', $labTest?->lab_technician_id);
        $testType = old('test_type', $labTest?->test_type);
        $results = old('results', $labTest?->results);
        $status = old('status', $labTest?->status ?? 'pending');
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
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Lab Technician</label>
            <select name="lab_technician_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $technicianId ? '' : 'selected' }}>Select technician</option>
                @foreach ($technicians as $t)
                    <option value="{{ $t->id }}" {{ (string) $technicianId === (string) $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Test type</label>
            <input name="test_type" value="{{ $testType }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Results (optional)</label>
            <textarea name="results" rows="4"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $results }}</textarea>
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
        <a href="{{ route('lab-tests.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
