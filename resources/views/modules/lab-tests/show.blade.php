@extends('layouts.app')

@section('title', 'Lab Test Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Lab Test #{{ $labTest->id }}</div>
                <div class="card-subtitle">
                    {{ $labTest->test_type }} •
                    <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $labTest->status) }}</span>
                </div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                <a href="{{ route('lab-tests.edit', $labTest) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('lab-tests.destroy', $labTest) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this lab test?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Patient</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($labTest->patient)->first_name }} {{ optional($labTest->patient)->last_name }}
                </div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                    {{ optional($labTest->patient)->contact_number }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Doctor</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($labTest->doctor)->name ?? '-' }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Technician</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($labTest->technician)->name ?? '-' }}
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Created</div>
                <div style="font-weight:600; margin-top:4px;">
                    {{ optional($labTest->created_at)->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>

        <div style="margin-top: 14px; padding:12px; border:1px solid var(--border-color); border-radius:14px;">
            <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Results</div>
            <div style="white-space:pre-wrap;">{{ $labTest->results ?: '-' }}</div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('lab-tests.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to lab tests
            </a>
        </div>
    </div>
@endsection

