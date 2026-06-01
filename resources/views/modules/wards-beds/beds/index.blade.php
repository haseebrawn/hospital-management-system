@extends('layouts.app')

@section('title', 'Beds')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Beds</div>
                <div class="card-subtitle">Manage bed inventory and status.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('beds.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search bed number"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:220px;">

                    <select name="ward_id"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All wards</option>
                        @foreach ($wards as $ward)
                            <option value="{{ $ward->id }}" {{ ($wardId ?? '') == $ward->id ? 'selected' : '' }}>
                                {{ $ward->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All status</option>
                        @foreach ($statusOptions as $opt)
                            <option value="{{ $opt }}" {{ ($status ?? '') === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>

                    @if (!empty($search) || !empty($wardId) || !empty($status))
                        <a href="{{ route('beds.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('beds.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Bed
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1020px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Bed</th>
                        <th>Ward</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($beds as $bed)
                        <tr>
                            <td class="u-nowrap">{{ $bed->id }}</td>
                            <td class="u-nowrap" style="font-weight:700;">{{ $bed->bed_number }}</td>
                            <td>{{ optional($bed->ward)->name ?? '-' }}</td>
                            <td>{{ optional(optional($bed->ward)->department)->name ?? '-' }}</td>
                            <td class="u-nowrap" style="text-transform:capitalize;">{{ $bed->status }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('beds.edit', $bed) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('beds.destroy', $bed) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this bed?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 16px;">No beds found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $beds->links() }}
        </div>

        <div style="margin-top: 14px;">
            <a href="{{ route('wards-beds.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to wards & beds
            </a>
        </div>
    </div>
@endsection
