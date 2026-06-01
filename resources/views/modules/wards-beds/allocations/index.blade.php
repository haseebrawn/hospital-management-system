@extends('layouts.app')

@section('title', 'Bed Allocations')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Bed Allocations</div>
                <div class="card-subtitle">Assign, release, and transfer beds.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('allocations.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <select name="patient_id"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff; min-width:240px;">
                        <option value="">All patients</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" {{ ($patientId ?? '') == $p->id ? 'selected' : '' }}>
                                #{{ $p->id }} — {{ $p->first_name }} {{ $p->last_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="bed_id"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff; min-width:240px;">
                        <option value="">All beds</option>
                        @foreach ($beds as $b)
                            <option value="{{ $b->id }}" {{ ($bedId ?? '') == $b->id ? 'selected' : '' }}>
                                #{{ $b->id }} — {{ $b->bed_number }} ({{ optional($b->ward)->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>

                    <select name="active"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All</option>
                        <option value="1" {{ ($active ?? '') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($active ?? '') === '0' ? 'selected' : '' }}>Released</option>
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>

                    @if (!empty($patientId) || !empty($bedId) || $active !== '')
                        <a href="{{ route('allocations.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('allocations.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + Assign Bed
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Patient Name</th>
                        <th>Bed</th>
                        <th>Ward</th>
                        <th>Assigned</th>
                        <th>Released</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allocations as $a)
                        <tr>
                            <td class="u-nowrap">{{ $a->id }}</td>
                            <td style="font-weight:600;">
                                {{ optional($a->patient)->first_name }} {{ optional($a->patient)->last_name }}
                            </td>
                            <td class="u-nowrap">{{ optional($a->bed)->bed_number ?? '-' }}</td>
                            <td>{{ optional(optional($a->bed)->ward)->name ?? '-' }}</td>
                            <td class="u-nowrap">{{ optional($a->assigned_at)->format('Y-m-d H:i') }}</td>
                            <td class="u-nowrap">{{ $a->released_at ? $a->released_at->format('Y-m-d H:i') : '-' }}</td>
                            <td style="text-align:right;">
                                @if (!$a->released_at)
                                    <form method="POST" action="{{ route('allocations.release', $a) }}" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                            onclick="return confirm('Release this bed?')"
                                            style="font-size:13px; color:#059669; background:transparent; border:none; cursor:pointer; margin-right:10px;">
                                            Release
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('allocations.transfer', $a) }}" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <select name="bed_id"
                                            style="padding:6px 8px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                                            @foreach ($beds as $b)
                                                @if ($b->status === 'available')
                                                    <option value="{{ $b->id }}">Transfer to: {{ $b->bed_number }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                            onclick="return confirm('Transfer to selected bed?')"
                                            style="font-size:13px; color: var(--primary); background:transparent; border:none; cursor:pointer;">
                                            Transfer
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:12px; color: var(--text-muted);">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 16px;">No allocations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $allocations->links() }}
        </div>

        <div style="margin-top: 14px;">
            <a href="{{ route('wards-beds.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to wards & beds
            </a>
        </div>
    </div>
@endsection
