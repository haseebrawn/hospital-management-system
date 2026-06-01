@extends('layouts.app')

@section('title', 'Create Patient')

@section('content')
    <div class="card">
        <div class="card-title">Create Patient</div>
        <div class="card-subtitle">Add a new patient record.</div>

        @include('modules.patients._form', [
            'patient' => null,
            'departments' => $departments,
            'action' => route('patients.store'),
            'method' => 'POST',
        ])
    </div>
@endsection

