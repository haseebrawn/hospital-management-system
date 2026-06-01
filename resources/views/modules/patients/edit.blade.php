@extends('layouts.app')

@section('title', 'Edit Patient')

@section('content')
    <div class="card">
        <div class="card-title">Edit Patient</div>
        <div class="card-subtitle">Update patient details.</div>

        @include('modules.patients._form', [
            'patient' => $patient,
            'departments' => $departments,
            'action' => route('patients.update', $patient),
            'method' => 'PUT',
        ])
    </div>
@endsection

