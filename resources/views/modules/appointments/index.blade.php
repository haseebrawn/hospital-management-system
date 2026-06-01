@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Appointments</div>
                <div class="card-subtitle">Manage appointments (pending/approved/completed/cancelled).</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('appointments.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient name / phone"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">

                    <select name="status"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $opt)
                            <option value="{{ $opt }}" {{ ($status ?? '') === $opt ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $opt)) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>

                    @if (!empty($search) || !empty($status))
                        <a href="{{ route('appointments.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('appointments.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Appointment
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1040px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appt)
                        <tr>
                            <td class="u-nowrap">{{ $appt->id }}</td>
                            <td class="u-nowrap">{{ $appt->date }}</td>
                            <td class="u-nowrap">{{ substr((string) $appt->time, 0, 5) }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('appointments.show', $appt) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($appt->patient)->first_name }} {{ optional($appt->patient)->last_name }}
                                </a>
                            </td>
                            <td>{{ optional($appt->doctor)->name ?? '-' }}</td>
                            <td>{{ optional($appt->department)->name ?? '-' }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ str_replace('_', ' ', $appt->status) }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('appointments.edit', $appt) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('appointments.destroy', $appt) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this appointment?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 16px;">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $appointments->links() }}
        </div>
    </div>
@endsection
