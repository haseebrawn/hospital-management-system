@extends('layouts.app')

@section('title', 'Create Prescription')

@section('content')
    <div class="card">
        <div class="card-title">Create Prescription</div>
        <div class="card-subtitle">Create a prescription from an appointment and review the linked patient and doctor context.</div>

        <div style="margin-top:16px;">
            @include('modules.prescriptions._form', [
                'prescription' => $prescription,
                'linkedAppointment' => $linkedAppointment,
                'appointments' => $appointments,
                'doctors' => $doctors,
                'medicines' => $medicines,
                'statusOptions' => $statusOptions,
                'action' => route('prescriptions.store'),
            ])
        </div>
    </div>
@endsection
