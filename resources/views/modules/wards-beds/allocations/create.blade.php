@extends('layouts.app')

@section('title', 'Assign Bed')

@section('content')
    <div class="card">
        <div class="card-title">Assign Bed</div>
        <div class="card-subtitle">Assign an available bed to a patient.</div>

        <form method="POST" action="{{ route('allocations.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div>
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Patient</label>
                    <select name="patient_id" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('patient_id') ? '' : 'selected' }}>Select patient</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" {{ (string) old('patient_id') === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->mrn ?? ('#' . $p->id) }} — {{ $p->first_name }} {{ $p->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Bed (available only)</label>
                    <select name="bed_id" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('bed_id') ? '' : 'selected' }}>Select bed</option>
                        @foreach ($beds as $b)
                            <option value="{{ $b->id }}" {{ (string) old('bed_id') === (string) $b->id ? 'selected' : '' }}>
                                {{ $b->bed_number }} ({{ optional($b->ward)->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
                <a href="{{ route('allocations.index') }}"
                    style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Cancel
                </a>
                <button type="submit"
                    style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                    Assign
                </button>
            </div>
        </form>
    </div>
@endsection
