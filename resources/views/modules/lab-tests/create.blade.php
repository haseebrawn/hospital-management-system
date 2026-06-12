@extends('layouts.app')

@section('title', 'Create Lab Test')

@section('content')
    <div class="card">
        <div class="card-title">Create Lab Test</div>
        <div class="card-subtitle">Add a new lab test request.</div>

        @include('modules.lab-tests._form', [
            'labTest' => $labTest,
            'patients' => $patients,
            'doctors' => $doctors,
            'technicians' => $technicians,
            'statusOptions' => $statusOptions,
            'action' => route('lab-tests.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
