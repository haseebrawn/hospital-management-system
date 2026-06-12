@extends('layouts.app')

@section('title', 'Medical Records')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Medical Records</div>
                <div class="card-subtitle">Track patient diagnosis, vitals, history, allergies, and doctor notes.</div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('medical-records.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient / diagnosis"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">

                    <select name="visit_type"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All visit types</option>
                        @foreach ($visitTypes as $type)
                            <option value="{{ $type }}" {{ ($visitType ?? '') === $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>
                </form>

                <a href="{{ route('medical-records.create') }}"
                    style="padding:8px 12px; border-radius:10px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Medical Record
                </a>
            </div>
        </div>

        <div style="margin-top:16px; overflow:auto;">
            <table class="dash-table" style="min-width:1120px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Visit</th>
                        <th>Complaint</th>
                        <th>Diagnosis</th>
                        <th>Follow-up</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td style="font-weight:700;">
                                <a href="{{ route('medical-records.show', $record) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($record->patient)->mrn ?? '-' }} —
                                    {{ optional($record->patient)->first_name }} {{ optional($record->patient)->last_name }}
                                </a>
                            </td>
                            <td>{{ optional($record->doctor)->name ?? '-' }}</td>
                            <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $record->visit_type) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($record->chief_complaint ?: '-', 35) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($record->diagnosis ?: '-', 45) }}</td>
                            <td>{{ optional($record->follow_up_date)->format('Y-m-d') ?? '-' }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('medical-records.edit', $record) }}"
                                    style="font-size:13px; color:var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('medical-records.destroy', $record) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this medical record?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:16px;">No medical records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $records->links() }}
        </div>
    </div>
@endsection
