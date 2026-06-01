@props([
    'bed' => null,
    'wards' => collect(),
    'statusOptions' => ['available', 'occupied', 'maintenance'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $wardId = old('ward_id', $bed?->ward_id);
        $bedNumber = old('bed_number', $bed?->bed_number);
        $status = old('status', $bed?->status ?? 'available');
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Ward</label>
            <select name="ward_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $wardId ? '' : 'selected' }}>Select ward</option>
                @foreach ($wards as $ward)
                    <option value="{{ $ward->id }}" {{ (string) $wardId === (string) $ward->id ? 'selected' : '' }}>
                        {{ $ward->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Status</label>
            <select name="status" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($statusOptions as $opt)
                    <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>
                        {{ ucfirst($opt) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Bed number</label>
            <input name="bed_number" value="{{ $bedNumber }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('beds.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>

