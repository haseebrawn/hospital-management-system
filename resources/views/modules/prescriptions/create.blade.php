@extends('layouts.app')

@section('title', 'Create Prescription')

@section('content')
    <div class="card">
        <div class="card-title">Create Prescription</div>
        <div class="card-subtitle">Create a prescription from an appointment.</div>

        <div style="margin-top:16px;">
            @include('modules.prescriptions._form', [
                'appointments' => $appointments,
                'doctors' => $doctors,
                'statusOptions' => $statusOptions,
                'action' => route('prescriptions.store'),
            ])
        </div>
    </div>
@endsection
