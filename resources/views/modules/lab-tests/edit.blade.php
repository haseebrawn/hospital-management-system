@extends('layouts.app')

@section('title', 'Edit Lab Test')

@section('content')
    <div class="card">
        <div class="card-title">Edit Lab Test</div>
        <div class="card-subtitle">Update test details, results, and the linked appointment, patient, and doctor context.</div>

        @include('modules.lab-tests._form', [
            'labTest' => $labTest,
            'linkedAppointment' => $linkedAppointment,
            'appointments' => $appointments,
            'patients' => $patients,
            'doctors' => $doctors,
            'technicians' => $technicians,
            'statusOptions' => $statusOptions,
            'action' => route('lab-tests.update', $labTest),
            'method' => 'PUT',
        ])
    </div>
@endsection
