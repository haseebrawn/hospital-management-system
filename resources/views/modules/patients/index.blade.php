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
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search MRN / name / phone"
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
                        <th class="u-nowrap table-col-id">ID</th>
                        <th class="u-nowrap table-col-id">MRN</th>
                        <th class="table-col-name">Patient Name</th>
                        <th class="u-nowrap">Phone</th>
                        <th class="table-col-status">Gender</th>
                        <th class="table-col-name">Department</th>
                        <th class="table-col-workflow">Workflow</th>
                        <th class="table-col-actions" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="u-nowrap table-col-id">{{ $patient->id }}</td>
                            <td class="u-nowrap table-col-id" style="font-weight:700;">{{ $patient->mrn ?? '-' }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('patients.show', $patient) }}" style="color:inherit; text-decoration:none;">
                                    {{ $patient->first_name }} {{ $patient->last_name }}
                                </a>
                            </td>
                            <td class="u-nowrap">{{ $patient->contact_number }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ $patient->gender }}</td>
                            <td>{{ optional($patient->department)->name ?? '-' }}</td>
                            <td>
                                @if ($patient->latestAppointment)
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div class="workflow-chip__meta">
                                            {{ $patient->latestAppointment->date }} {{ substr((string) $patient->latestAppointment->time, 0, 5) }}
                                        </div>
                                        <div class="workflow-chip-row" style="max-width: 340px;">
                                            @foreach ($patient->latestAppointment->workflowTimeline ?? [] as $step)
                                                <span class="workflow-chip"
                                                    style="--workflow-chip-border: {{ $step['done'] ? 'rgba(34,197,94,0.24)' : 'rgba(148,163,184,0.24)' }}; --workflow-chip-bg: {{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; --workflow-chip-color: {{ $step['done'] ? '#166534' : '#64748b' }}; --workflow-chip-dot: {{ $step['done'] ? '#22c55e' : '#cbd5e1' }};">
                                                    <span class="workflow-chip__dot"></span>
                                                    {{ $step['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <a href="{{ route('patients.history', $patient) }}" style="font-size:12px; color:var(--primary); text-decoration:none;">Open history</a>
                                    </div>
                                @else
                                    <span style="font-size:12px; color:var(--text-muted);">No appointments yet</span>
                                @endif
                            </td>
                            <td class="table-col-actions" style="text-align:right;">
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
                            <td colspan="8" style="padding: 16px;">No patients found.</td>
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
