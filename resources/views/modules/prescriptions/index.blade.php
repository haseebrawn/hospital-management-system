@extends('layouts.app')

@section('title', 'Prescriptions')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Prescriptions</div>
                <div class="card-subtitle">Create, review, and manage patient prescriptions.</div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('prescriptions.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient / medicine"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">

                    <select name="status"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option }}" {{ ($status ?? '') === $option ? 'selected' : '' }}>
                                {{ ucfirst($option) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>
                </form>

                <a href="{{ route('prescriptions.create') }}"
                    style="padding:8px 12px; border-radius:10px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Prescription
                </a>
            </div>
        </div>

        <div style="margin-top:16px; overflow:auto;">
            <table class="dash-table" style="min-width:1260px;">
                <thead>
                    <tr>
                        <th class="table-col-id">ID</th>
                        <th class="table-col-name">Patient</th>
                        <th class="table-col-name">Doctor</th>
                        <th class="table-col-name">Instructions</th>
                        <th class="table-col-name">Medicines</th>
                        <th class="table-col-status">Status</th>
                        <th class="table-col-date">Created</th>
                        <th class="table-col-workflow">Workflow</th>
                        <th class="table-col-actions" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        <tr>
                            <td class="table-col-id">{{ $prescription->id }}</td>
                            <td style="font-weight:700;">
                                <a href="{{ route('prescriptions.show', $prescription) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($prescription->patient)->mrn ?? '-' }} —
                                    {{ optional($prescription->patient)->first_name }} {{ optional($prescription->patient)->last_name }}
                                </a>
                            </td>
                            <td>{{ optional($prescription->doctor)->name ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($prescription->description ?: '-', 45) }}</td>
                            <td>
                                @if ($prescription->items->isNotEmpty())
                                    {{ \Illuminate\Support\Str::limit($prescription->items->pluck('medicine_name')->join(', '), 45) }}
                                @else
                                    {{ \Illuminate\Support\Str::limit($prescription->medicines ?: '-', 45) }}
                                @endif
                            </td>
                            <td class="table-col-status" style="text-transform:capitalize; font-weight:700;">{{ $prescription->status }}</td>
                            <td class="table-col-date">{{ optional($prescription->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                @if ($prescription->appointment)
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div class="workflow-chip__meta">
                                            Appt #{{ $prescription->appointment->id }} · {{ $prescription->appointment->date }}
                                        </div>
                                        <div class="workflow-chip-row" style="max-width: 420px;">
                                            @foreach ($prescription->appointment->workflowTimeline ?? [] as $step)
                                                <span class="workflow-chip"
                                                    style="--workflow-chip-border: {{ $step['done'] ? 'rgba(34,197,94,0.24)' : 'rgba(148,163,184,0.24)' }}; --workflow-chip-bg: {{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; --workflow-chip-color: {{ $step['done'] ? '#166534' : '#64748b' }}; --workflow-chip-dot: {{ $step['done'] ? '#22c55e' : '#cbd5e1' }};">
                                                    <span class="workflow-chip__dot"></span>
                                                    {{ $step['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <a href="{{ route('appointments.show', $prescription->appointment) }}" style="font-size:12px; color:var(--primary); text-decoration:none;">Open appointment</a>
                                    </div>
                                @else
                                    <span style="font-size:12px; color:var(--text-muted);">No linked appointment</span>
                                @endif
                            </td>
                            <td class="table-col-actions" style="text-align:right;">
                                <a href="{{ route('prescriptions.edit', $prescription) }}"
                                    style="font-size:13px; color:var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('prescriptions.destroy', $prescription) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this prescription?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:16px;">No prescriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $prescriptions->links() }}
        </div>
    </div>
@endsection
