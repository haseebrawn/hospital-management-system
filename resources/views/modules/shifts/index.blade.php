@extends('layouts.app')

@section('title', 'Shifts')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Shifts</div>
                <div class="card-subtitle">View and assign staff shifts.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('shifts.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <select name="staff_id"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff; min-width:240px;">
                        <option value="">All staff</option>
                        @foreach ($staffOptions as $s)
                            <option value="{{ $s->id }}" {{ ($staffId ?? '') == $s->id ? 'selected' : '' }}>
                                #{{ $s->id }} — {{ optional($s->user)->name }}
                            </option>
                        @endforeach
                    </select>

                    <input type="date" name="shift_date" value="{{ $shiftDate ?? '' }}"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>

                    @if (!empty($staffId) || !empty($shiftDate))
                        <a href="{{ route('shifts.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('shifts.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + Assign Shift
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1050px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Staff Name</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Start</th>
                        <th>End</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr>
                            <td class="u-nowrap">{{ $shift->id }}</td>
                            <td style="font-weight:600;">{{ optional(optional($shift->staff)->user)->name ?? '-' }}</td>
                            <td>{{ optional(optional($shift->staff)->department)->name ?? '-' }}</td>
                            <td class="u-nowrap">{{ $shift->shift_date }}</td>
                            <td class="u-nowrap">{{ $shift->shift_name }}</td>
                            <td class="u-nowrap">{{ substr((string) $shift->shift_start, 0, 5) }}</td>
                            <td class="u-nowrap">{{ substr((string) $shift->shift_end, 0, 5) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 16px;">No shifts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $shifts->links() }}
        </div>
    </div>
@endsection
