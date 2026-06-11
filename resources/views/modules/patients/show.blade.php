@extends('layouts.app')

@section('title', 'Patient Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">
                    {{ $patient->first_name }} {{ $patient->last_name }}
                </div>
                <div class="card-subtitle">Patient profile</div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                <a href="{{ route('patients.edit', $patient) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this patient?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">MRN / Registration No.</div>
                <div style="font-weight:600; margin-top:4px;">{{ $patient->mrn ?? '-' }}</div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Contact</div>
                <div style="font-weight:600; margin-top:4px;">{{ $patient->contact_number }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Gender</div>
                <div style="font-weight:600; margin-top:4px; text-transform:capitalize;">{{ $patient->gender }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Department</div>
                <div style="font-weight:600; margin-top:4px;">{{ optional($patient->department)->name ?? '-' }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Address</div>
                <div style="font-weight:600; margin-top:4px;">{{ $patient->address ?: '-' }}</div>
            </div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('patients.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to patients
            </a>
        </div>
    </div>
@endsection
