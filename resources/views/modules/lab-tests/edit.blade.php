@extends('layouts.app')

@section('title', 'Edit Lab Test')

@section('content')
    <div class="card">
        <div class="card-title">Edit Lab Test</div>
        <div class="card-subtitle">Update test details and results.</div>

        @include('modules.lab-tests._form', [
            'labTest' => $labTest,
            'patients' => $patients,
            'doctors' => $doctors,
            'technicians' => $technicians,
            'statusOptions' => $statusOptions,
            'action' => route('lab-tests.update', $labTest),
            'method' => 'PUT',
        ])
    </div>
@endsection

