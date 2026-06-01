@extends('layouts.app')

@section('title', 'Create Appointment')

@section('content')
    <div class="card">
        <div class="card-title">Create Appointment</div>
        <div class="card-subtitle">Schedule a new appointment.</div>

        @include('modules.appointments._form', [
            'appointment' => null,
            'patients' => $patients,
            'doctors' => $doctors,
            'departments' => $departments,
            'statusOptions' => $statusOptions,
            'action' => route('appointments.store'),
            'method' => 'POST',
        ])
    </div>
@endsection

