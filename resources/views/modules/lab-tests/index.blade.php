@extends('layouts.app')

@section('title', 'Lab Tests')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Lab Tests</div>
                <div class="card-subtitle">Create, update results, and track lab test status.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('lab-tests.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient / test type"
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
                        <a href="{{ route('lab-tests.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('lab-tests.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Lab Test
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1260px;">
                <thead>
                    <tr>
                        <th class="u-nowrap table-col-id">ID</th>
                        <th class="table-col-name">Test Type</th>
                        <th class="table-col-name">Patient Name</th>
                        <th class="table-col-name">Doctor</th>
                        <th class="table-col-name">Technician</th>
                        <th class="table-col-status">Status</th>
                        <th class="table-col-workflow">Workflow</th>
                        <th class="table-col-actions" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tests as $test)
                        <tr>
                            <td class="u-nowrap table-col-id">{{ $test->id }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('lab-tests.show', $test) }}" style="color:inherit; text-decoration:none;">
                                    {{ $test->test_type }}
                                </a>
                            </td>
                            <td>{{ optional($test->patient)->first_name }} {{ optional($test->patient)->last_name }}</td>
                            <td>{{ optional($test->doctor)->name ?? '-' }}</td>
                            <td>{{ optional($test->technician)->name ?? '-' }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ str_replace('_', ' ', $test->status) }}</td>
                            <td>
                                @if ($test->appointment)
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div class="workflow-chip__meta">
                                            Appt #{{ $test->appointment->id }} · {{ $test->appointment->date }}
                                        </div>
                                        <div class="workflow-chip-row" style="max-width: 420px;">
                                            @foreach ($test->appointment->workflowTimeline ?? [] as $step)
                                                <span class="workflow-chip"
                                                    style="--workflow-chip-border: {{ $step['done'] ? 'rgba(34,197,94,0.24)' : 'rgba(148,163,184,0.24)' }}; --workflow-chip-bg: {{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; --workflow-chip-color: {{ $step['done'] ? '#166534' : '#64748b' }}; --workflow-chip-dot: {{ $step['done'] ? '#22c55e' : '#cbd5e1' }};">
                                                    <span class="workflow-chip__dot"></span>
                                                    {{ $step['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <a href="{{ route('appointments.show', $test->appointment) }}" style="font-size:12px; color:var(--primary); text-decoration:none;">Open appointment</a>
                                    </div>
                                @else
                                    <span style="font-size:12px; color:var(--text-muted);">No linked appointment</span>
                                @endif
                            </td>
                            <td class="table-col-actions" style="text-align:right;">
                                <a href="{{ route('lab-tests.edit', $test) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('lab-tests.destroy', $test) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this lab test?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 16px;">No lab tests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $tests->links() }}
        </div>
    </div>
@endsection
