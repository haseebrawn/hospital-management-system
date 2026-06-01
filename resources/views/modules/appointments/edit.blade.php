@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')
    <div class="card">
        <div class="card-title">Edit Appointment</div>
        <div class="card-subtitle">Update appointment details.</div>

        @include('modules.appointments._form', [
            'appointment' => $appointment,
            'patients' => $patients,
            'doctors' => $doctors,
            'departments' => $departments,
            'statusOptions' => $statusOptions,
            'action' => route('appointments.update', $appointment),
            'method' => 'PUT',
        ])
    </div>
@endsection

