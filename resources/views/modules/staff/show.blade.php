@extends('layouts.app')

@section('title', 'Staff Profile')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">{{ optional($staff->user)->name }}</div>
                <div class="card-subtitle">
                    {{ $staff->designation }} • <span style="text-transform:capitalize;">{{ $staff->employment_status }}</span>
                </div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                <a href="{{ route('staff.edit', $staff) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('staff.destroy', $staff) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this staff profile?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Email</div>
                <div style="font-weight:600; margin-top:4px;">{{ optional($staff->user)->email }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Department</div>
                <div style="font-weight:600; margin-top:4px;">{{ optional($staff->department)->name ?? '-' }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Salary</div>
                <div style="font-weight:600; margin-top:4px;">{{ number_format((float) $staff->salary, 2) }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Joining date</div>
                <div style="font-weight:600; margin-top:4px;">{{ $staff->joining_date }}</div>
            </div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('staff.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to staff
            </a>
        </div>
    </div>
@endsection

