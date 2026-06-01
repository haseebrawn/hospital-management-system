@props([
    'patient' => null,
    'departments' => collect(),
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">First name</label>
            <input name="first_name"
                value="{{ old('first_name', $patient?->first_name) }}"
                required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Last name</label>
            <input name="last_name"
                value="{{ old('last_name', $patient?->last_name) }}"
                required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Contact number</label>
            <input name="contact_number"
                value="{{ old('contact_number', $patient?->contact_number) }}"
                required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Gender</label>
            @php
                $gender = old('gender', $patient?->gender);
            @endphp
            <select name="gender"
                required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $gender ? '' : 'selected' }}>Select gender</option>
                <option value="male" {{ $gender === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ $gender === 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ $gender === 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Department</label>
            @php
                $departmentId = old('department_id', $patient?->department_id);
            @endphp
            <select name="department_id"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="">— None —</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Address</label>
            <input name="address"
                value="{{ old('address', $patient?->address) }}"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('patients.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>

