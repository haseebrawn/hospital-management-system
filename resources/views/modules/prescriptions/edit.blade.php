@extends('layouts.app')

@section('title', 'Edit Prescription')

@section('content')
    <div class="card">
        <div class="card-title">Edit Prescription</div>
        <div class="card-subtitle">Update prescription details, status, and the linked appointment context.</div>

        <div style="margin-top:16px;">
            @include('modules.prescriptions._form', [
                'prescription' => $prescription,
                'linkedAppointment' => $linkedAppointment,
                'appointments' => $appointments,
                'doctors' => $doctors,
                'medicines' => $medicines,
                'statusOptions' => $statusOptions,
                'action' => route('prescriptions.update', $prescription),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
