@extends('layouts.app')

@section('title', 'Assign Shift')

@section('content')
    <div class="card">
        <div class="card-title">Assign Shift</div>
        <div class="card-subtitle">Assign a shift to a staff member.</div>

        <form method="POST" action="{{ route('shifts.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Staff</label>
                    <select name="staff_id" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('staff_id') ? '' : 'selected' }}>Select staff</option>
                        @foreach ($staffOptions as $s)
                            <option value="{{ $s->id }}" {{ (string) old('staff_id') === (string) $s->id ? 'selected' : '' }}>
                                #{{ $s->id }} — {{ optional($s->user)->name }} ({{ optional($s->department)->name ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Shift name</label>
                    <select name="shift_name" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        @foreach ($shiftNameOptions as $opt)
                            <option value="{{ $opt }}" {{ old('shift_name', 'Morning') === $opt ? 'selected' : '' }}>
                                {{ $opt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Shift date</label>
                    <input type="date" name="shift_date" value="{{ old('shift_date') }}" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Start</label>
                    <input type="time" name="shift_start" value="{{ old('shift_start') }}" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">End</label>
                    <input type="time" name="shift_end" value="{{ old('shift_end') }}" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
                <a href="{{ route('shifts.index') }}"
                    style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Cancel
                </a>
                <button type="submit"
                    style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                    Assign
                </button>
            </div>
        </form>
    </div>
@endsection

