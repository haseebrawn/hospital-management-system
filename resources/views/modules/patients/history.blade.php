@extends('layouts.app')

@section('title', 'Patient History')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">
                    {{ $patient->first_name }} {{ $patient->last_name }} - Medical History
                </div>
                <div class="card-subtitle">
                    MRN {{ $patient->mrn ?? '-' }} | {{ optional($patient->department)->name ?? 'No department' }}
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('patients.show', $patient) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Back to profile
                </a>
                <a href="{{ route('medical-records.create', ['appointment_id' => optional($appointments->first())->id]) }}"
                    style="padding:8px 12px; border-radius:10px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                    Add Medical Record
                </a>
            </div>
        </div>

        <div style="margin-top:16px; display:grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap:14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Appointments</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $appointments->count() }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Medical Records</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $medicalRecords->count() }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Prescriptions</div>
                <div style="font-weight:900; margin-top:4px; font-size:18px;">{{ $prescriptions->count() }}</div>
            </div>
        </div>

        <div style="margin-top:16px; display:grid; grid-template-columns: 1fr; gap:14px;">
            <div style="padding:14px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-weight:800; margin-bottom:12px;">Recent Medical Records</div>
                <div style="overflow:auto;">
                    <table class="dash-table" style="min-width:900px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Visit</th>
                                <th>Doctor</th>
                                <th>Complaint</th>
                                <th>Diagnosis</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($medicalRecords as $record)
                                <tr>
                                    <td>{{ optional($record->created_at)->format('Y-m-d') }}</td>
                                    <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $record->visit_type) }}</td>
                                    <td>{{ optional($record->doctor)->name ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($record->chief_complaint ?: '-', 40) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($record->diagnosis ?: '-', 50) }}</td>
                                    <td><a href="{{ route('medical-records.show', $record) }}" style="color:var(--primary); text-decoration:none;">View</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:16px;">No medical records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="padding:14px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-weight:800; margin-bottom:12px;">Recent Prescriptions</div>
                <div style="overflow:auto;">
                    <table class="dash-table" style="min-width:900px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Doctor</th>
                                <th>Description</th>
                                <th>Medicines</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prescriptions as $prescription)
                                <tr>
                                    <td>{{ optional($prescription->created_at)->format('Y-m-d') }}</td>
                                    <td style="text-transform:capitalize;">{{ $prescription->status }}</td>
                                    <td>{{ optional($prescription->doctor)->name ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($prescription->description ?: '-', 50) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($prescription->medicines ?: '-', 50) }}</td>
                                    <td><a href="{{ route('prescriptions.show', $prescription) }}" style="color:var(--primary); text-decoration:none;">View</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:16px;">No prescriptions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="padding:14px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:12px;">
                    <div>
                        <div style="font-weight:800;">Recent Appointments</div>
                        <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Each appointment shows the same care workflow timeline.</div>
                    </div>
                    <div style="font-size:12px; color:var(--text-muted);">Check-in → Record → Prescription → Lab → Billing</div>
                </div>
                <div style="overflow:auto;">
                    <table class="dash-table" style="min-width:1100px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Doctor</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Workflow</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->date }}</td>
                                    <td>{{ substr((string) $appointment->time, 0, 5) }}</td>
                                    <td>{{ optional($appointment->doctor)->name ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($appointment->reason ?: '-', 50) }}</td>
                                    <td style="text-transform:capitalize;">{{ $appointment->status }}</td>
                                    <td>
                                        <div style="display:flex; flex-wrap:wrap; gap:6px; max-width:420px;">
                                            @foreach ($appointment->workflowTimeline ?? [] as $step)
                                                <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; font-size:12px; border:1px solid {{ $step['done'] ? 'rgba(34,197,94,0.28)' : 'rgba(148,163,184,0.28)' }}; background:{{ $step['done'] ? 'rgba(34,197,94,0.08)' : 'rgba(248,250,252,0.95)' }}; color:{{ $step['done'] ? '#166534' : '#64748b' }};">
                                                    <span style="width:8px; height:8px; border-radius:999px; background:{{ $step['done'] ? '#22c55e' : '#cbd5e1' }};"></span>
                                                    {{ $step['label'] }}
                                                    <span style="font-size:11px; opacity:.85;">{{ $step['done'] ? 'Done' : 'Pending' }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td><a href="{{ route('appointments.show', $appointment) }}" style="color:var(--primary); text-decoration:none;">View</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding:16px;">No appointments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
