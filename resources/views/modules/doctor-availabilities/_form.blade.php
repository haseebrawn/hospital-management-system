@props([
    'availability' => null,
    'doctors' => collect(),
    'dayOptions' => [],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $doctorId = old('doctor_id', $availability?->doctor_id);
        $dayOfWeek = old('day_of_week', $availability?->day_of_week);
        $startTime = old('start_time', $availability?->start_time ? substr((string) $availability->start_time, 0, 5) : null);
        $endTime = old('end_time', $availability?->end_time ? substr((string) $availability->end_time, 0, 5) : null);
        $isActive = old('is_active', $availability?->is_active ?? true);
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        @if (! auth()->user()->hasRole('doctor'))
            <div style="grid-column: 1 / -1;">
                <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Doctor</label>
                <select name="doctor_id" required
                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                    <option value="" disabled {{ $doctorId ? '' : 'selected' }}>Select doctor</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->name }} — {{ optional($doctor->department)->name ?? 'No department' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Day</label>
            <select name="day_of_week" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $dayOfWeek === null || $dayOfWeek === '' ? 'selected' : '' }}>Select day</option>
                @foreach ($dayOptions as $value => $label)
                    <option value="{{ $value }}" {{ (string) $dayOfWeek === (string) $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Status</label>
            <select name="is_active"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="1" {{ (string) $isActive === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (string) $isActive === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Start Time</label>
            <input type="time" name="start_time" value="{{ $startTime }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">End Time</label>
            <input type="time" name="end_time" value="{{ $endTime }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('doctor-availabilities.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
