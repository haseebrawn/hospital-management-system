@extends('layouts.app')

@section('title', 'Staff Report')

@section('content')
    <div class="card">
        <div class="card-title">Staff Report</div>
        <div class="card-subtitle">Staff directory based on your role and department access.</div>

        <form method="GET" action="{{ route('reports.staff') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Department</div>
                <select name="department_id"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff; min-width:260px;">
                    <option value="">All departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                Apply
            </button>
            <a href="{{ route('reports.staff') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 14px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td style="font-weight:700;">{{ optional($s->user)->name }}</td>
                            <td>{{ optional($s->user)->email }}</td>
                            <td>{{ optional($s->department)->name ?? '-' }}</td>
                            <td>{{ $s->designation }}</td>
                            <td style="text-transform:capitalize;">{{ $s->employment_status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 16px;">No staff found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $staff->links() }}
        </div>

        <div style="margin-top: 14px;">
            <a href="{{ route('reports.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to reports
            </a>
        </div>
    </div>
@endsection
