@extends('layouts.app')

@section('title', 'Admin Appointments')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Appointment Approval Panel</div>
                <div class="card-subtitle">Review pending appointments and update workflow status from the admin panel.</div>
            </div>

            <form method="GET" action="{{ route('admin.appointments.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient name / phone"
                    style="padding:9px 11px; border:1px solid var(--border-color); border-radius:11px; font-size:13px; min-width:240px;">

                <select name="status"
                    style="padding:9px 11px; border:1px solid var(--border-color); border-radius:11px; font-size:13px; background:#fff;">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $opt)
                        <option value="{{ $opt }}" {{ ($status ?? '') === $opt ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $opt)) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    style="padding:9px 13px; border-radius:11px; border:1px solid var(--border-color); background:#fff; cursor:pointer; font-weight:700;">
                    Filter
                </button>
            </form>
        </div>

        <div style="margin-top:16px; overflow:auto;">
            <table class="dash-table" style="min-width:1120px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Phone</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th style="text-align:right;">Admin Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td style="font-weight:700;">
                                <a href="{{ route('appointments.show', $appointment) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($appointment->patient)->first_name }} {{ optional($appointment->patient)->last_name }}
                                </a>
                            </td>
                            <td>{{ optional($appointment->patient)->contact_number ?? '-' }}</td>
                            <td>{{ optional($appointment->doctor)->name ?? '-' }}</td>
                            <td>{{ optional($appointment->department)->name ?? '-' }}</td>
                            <td>{{ $appointment->date }} {{ substr((string) $appointment->time, 0, 5) }}</td>
                            <td style="text-transform:capitalize; font-weight:700;">{{ str_replace('_', ' ', $appointment->status) }}</td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}"
                                    style="display:inline-flex; gap:8px; align-items:center; justify-content:flex-end;">
                                    @csrf
                                    @method('PUT')
                                    <select name="status"
                                        style="padding:7px 9px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                                        @foreach ($statusOptions as $opt)
                                            <option value="{{ $opt }}" {{ $appointment->status === $opt ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $opt)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        style="padding:7px 11px; border-radius:10px; border:1px solid rgba(37,99,235,0.25); background:rgba(37,99,235,0.08); color:var(--primary); cursor:pointer; font-weight:800;">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:16px;">No appointments found for this admin panel filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $appointments->links() }}
        </div>
    </div>
@endsection
