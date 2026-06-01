@props([
    'ward' => null,
    'departments' => collect(),
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $name = old('name', $ward?->name);
        $departmentId = old('department_id', $ward?->department_id);
        $capacity = old('capacity', $ward?->capacity ?? 0);
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Name</label>
            <input name="name" value="{{ $name }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Department (optional)</label>
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

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Capacity</label>
            <input type="number" min="1" name="capacity" value="{{ $capacity }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('wards.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>

