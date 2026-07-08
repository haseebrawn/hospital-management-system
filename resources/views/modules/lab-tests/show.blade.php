@extends('layouts.app')

@section('title', 'Lab Test Details')

@section('content')
    <div class="card">
        <div class="page-header">
            <div>
                <div class="card-title">Lab Test #{{ $labTest->id }}</div>
                <div class="card-subtitle">
                    {{ $labTest->test_type }} •
                    <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $labTest->status) }}</span>
                </div>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('lab-tests.edit', $labTest) }}" class="page-button page-button--neutral">
                    Edit
                </a>
                <form method="POST" action="{{ route('lab-tests.destroy', $labTest) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this lab test?')"
                        class="page-button"
                        style="border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-weight:700;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px;" class="page-grid-2">
            <div class="info-card">
                <div class="info-card__label">Patient</div>
                <div class="info-card__value">
                    {{ optional($labTest->patient)->first_name }} {{ optional($labTest->patient)->last_name }}
                </div>
                <div class="section-panel__muted" style="margin-top:2px;">
                    {{ optional($labTest->patient)->contact_number }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Doctor</div>
                <div class="info-card__value">
                    {{ optional($labTest->doctor)->name ?? '-' }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Technician</div>
                <div class="info-card__value">
                    {{ optional($labTest->technician)->name ?? '-' }}
                </div>
            </div>

            <div class="info-card">
                <div class="info-card__label">Created</div>
                <div class="info-card__value">
                    {{ optional($labTest->created_at)->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>

        <div style="margin-top: 16px;" class="section-panel">
            <div class="section-panel__title">Results</div>
            <div style="white-space:pre-wrap;">{{ $labTest->results ?: '-' }}</div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('lab-tests.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to lab tests
            </a>
        </div>
    </div>
@endsection
