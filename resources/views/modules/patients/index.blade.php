@extends('layouts.app')

@section('title', 'Patients')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Patients</div>
                <div class="card-subtitle">Search, view, create, and manage patients.</div>
            </div>
            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('patients.index') }}" style="display:flex; gap:10px; align-items:center;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search name / phone"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">
                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Search
                    </button>
                    @if (!empty($search))
                        <a href="{{ route('patients.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('patients.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Patient
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 920px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Patient Name</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="u-nowrap">{{ $patient->id }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('patients.show', $patient) }}" style="color:inherit; text-decoration:none;">
                                    {{ $patient->first_name }} {{ $patient->last_name }}
                                </a>
                            </td>
                            <td class="u-nowrap">{{ $patient->contact_number }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ $patient->gender }}</td>
                            <td>{{ optional($patient->department)->name ?? '-' }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('patients.edit', $patient) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('patients.destroy', $patient) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this patient?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 16px;">No patients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $patients->links() }}
        </div>
    </div>
@endsection
