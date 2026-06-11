@extends('layouts.app')

@section('title', 'Doctor Availability')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Doctor Availability</div>
                <div class="card-subtitle">Manage weekly doctor slots used when booking appointments.</div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('doctor-availabilities.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    @if (! auth()->user()->hasRole('doctor'))
                        <select name="doctor_id"
                            style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                            <option value="">All doctors</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <select name="day_of_week"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All days</option>
                        @foreach ($dayOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string) $day === (string) $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>
                </form>

                <a href="{{ route('doctor-availabilities.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + Add Availability
                </a>
            </div>
        </div>

        <div style="margin-top:16px; overflow:auto;">
            <table class="dash-table" style="min-width:920px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Day</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($availabilities as $availability)
                        <tr>
                            <td>{{ $availability->id }}</td>
                            <td style="font-weight:700;">{{ optional($availability->doctor)->name ?? '-' }}</td>
                            <td>{{ optional($availability->doctor?->department)->name ?? '-' }}</td>
                            <td>{{ $availability->day_name }}</td>
                            <td>{{ substr((string) $availability->start_time, 0, 5) }}</td>
                            <td>{{ substr((string) $availability->end_time, 0, 5) }}</td>
                            <td>{{ $availability->is_active ? 'Active' : 'Inactive' }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('doctor-availabilities.edit', $availability) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('doctor-availabilities.destroy', $availability) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this availability slot?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:16px;">No doctor availability slots found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $availabilities->links() }}
        </div>
    </div>
@endsection
