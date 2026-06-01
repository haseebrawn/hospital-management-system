@extends('layouts.app')

@section('title', 'Staff')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Staff</div>
                <div class="card-subtitle">Manage staff profiles.</div>
            </div>

            <div style="display:flex; gap: 10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('staff.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search name / email"
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

                    @if (!empty($search) || !empty($departmentId) || !empty($status))
                        <a href="{{ route('staff.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                <a href="{{ route('staff.create') }}"
                    style="padding:8px 12px; border-radius:10px; background: var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Staff
                </a>
            </div>
        </div>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1100px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Status</th>
                        <th>Joining date</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('staff.show', $s) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($s->user)->name }}
                                </a>
                                <div style="font-size:12px; color: var(--text-muted);">{{ optional($s->user)->email }}</div>
                            </td>
                            <td>{{ optional($s->department)->name ?? '-' }}</td>
                            <td>{{ $s->designation }}</td>
                            <td style="text-transform:capitalize;">{{ $s->employment_status }}</td>
                            <td>{{ $s->joining_date }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('staff.edit', $s) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('staff.destroy', $s) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this staff profile?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 16px;">No staff found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $staff->links() }}
        </div>
    </div>
@endsection

