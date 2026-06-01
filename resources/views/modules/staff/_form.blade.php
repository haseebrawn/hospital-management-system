@props([
    'staff' => null,
    'departments' => collect(),
    'statusOptions' => ['active', 'terminated', 'resigned'],
    'users' => null,
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $userId = old('user_id', $staff?->user_id);
        $departmentId = old('department_id', $staff?->department_id);
        $designation = old('designation', $staff?->designation);
        $salary = old('salary', $staff?->salary ?? 0);
        $joiningDate = old('joining_date', $staff?->joining_date);
        $employmentStatus = old('employment_status', $staff?->employment_status ?? 'active');
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        @if ($users !== null)
            <div style="grid-column: 1 / -1;">
                <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">User</label>
                <select name="user_id" required
                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                    <option value="" disabled {{ $userId ? '' : 'selected' }}>Select user</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ (string) $userId === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <div style="grid-column: 1 / -1;">
                <div style="font-size:12px; color: var(--text-muted);">User</div>
                <div style="font-weight:700; margin-top:4px;">{{ optional($staff->user)->name }}</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">{{ optional($staff->user)->email }}</div>
            </div>
        @endif

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Department</label>
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
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Employment status</label>
            <select name="employment_status" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($statusOptions as $opt)
                    <option value="{{ $opt }}" {{ $employmentStatus === $opt ? 'selected' : '' }}>
                        {{ ucfirst($opt) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Designation</label>
            <input name="designation" value="{{ $designation }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Salary</label>
            <input type="number" step="0.01" min="0" name="salary" value="{{ $salary }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Joining date</label>
            <input type="date" name="joining_date" value="{{ $joiningDate }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('staff.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>

