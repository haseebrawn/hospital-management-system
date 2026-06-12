@extends('layouts.app')

@section('title', 'Prescriptions')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Prescriptions</div>
                <div class="card-subtitle">Create, review, and manage patient prescriptions.</div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <form method="GET" action="{{ route('prescriptions.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input name="q" value="{{ $search ?? '' }}" placeholder="Search patient / medicine"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; min-width:240px;">

                    <select name="status"
                        style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option }}" {{ ($status ?? '') === $option ? 'selected' : '' }}>
                                {{ ucfirst($option) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        Filter
                    </button>
                </form>

                <a href="{{ route('prescriptions.create') }}"
                    style="padding:8px 12px; border-radius:10px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    + New Prescription
                </a>
            </div>
        </div>

        <div style="margin-top:16px; overflow:auto;">
            <table class="dash-table" style="min-width:1040px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Instructions</th>
                        <th>Medicines</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        <tr>
                            <td>{{ $prescription->id }}</td>
                            <td style="font-weight:700;">
                                <a href="{{ route('prescriptions.show', $prescription) }}" style="color:inherit; text-decoration:none;">
                                    {{ optional($prescription->patient)->mrn ?? '-' }} —
                                    {{ optional($prescription->patient)->first_name }} {{ optional($prescription->patient)->last_name }}
                                </a>
                            </td>
                            <td>{{ optional($prescription->doctor)->name ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($prescription->description ?: '-', 45) }}</td>
                            <td>
                                @if ($prescription->items->isNotEmpty())
                                    {{ \Illuminate\Support\Str::limit($prescription->items->pluck('medicine_name')->join(', '), 45) }}
                                @else
                                    {{ \Illuminate\Support\Str::limit($prescription->medicines ?: '-', 45) }}
                                @endif
                            </td>
                            <td style="text-transform:capitalize; font-weight:700;">{{ $prescription->status }}</td>
                            <td>{{ optional($prescription->created_at)->format('Y-m-d H:i') }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('prescriptions.edit', $prescription) }}"
                                    style="font-size:13px; color:var(--primary); text-decoration:none; margin-right:10px;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('prescriptions.destroy', $prescription) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this prescription?')"
                                        style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:16px;">No prescriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $prescriptions->links() }}
        </div>
    </div>
@endsection
