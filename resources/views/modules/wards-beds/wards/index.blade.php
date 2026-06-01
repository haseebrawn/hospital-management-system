@extends('layouts.app')

@section('title', 'Wards')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Wards</div>
                <div class="card-subtitle">Manage wards and capacity.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('wards.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search ward name"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:220px;">

                    <select name="department_id"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($departmentId ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>

                    @if (!empty($search) || !empty($departmentId))
                        <a href="{{ route('wards.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('wards.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Ward
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Capacity</th>
                        <th>Beds</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($wards as $ward)
                        <tr>
                            <td class="u-nowrap">{{ $ward->id }}</td>
                            <td style="font-weight:600;">{{ $ward->name }}</td>
                            <td>{{ optional($ward->department)->name ?? '-' }}</td>
                            <td class="u-nowrap">{{ $ward->capacity }}</td>
                            <td class="u-nowrap">{{ $ward->beds_count }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('wards.edit', $ward) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('wards.destroy', $ward) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this ward?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 16px;">No wards found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $wards->links() }}
        </div>

        <div style="margin-top: 14px;">
            <a href="{{ route('wards-beds.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to wards & beds
            </a>
        </div>
    </div>
@endsection
