@extends('layouts.app')

@section('title', 'Create Bed')

@section('content')
    <div class="card">
        <div class="card-title">Create Bed</div>
        <div class="card-subtitle">Add a bed to a ward (capacity enforced).</div>

        @include('modules.wards-beds.beds._form', [
            'bed' => null,
            'wards' => $wards,
            'statusOptions' => $statusOptions,
            'action' => route('beds.store'),
            'method' => 'POST',
        ])
    </div>
@endsection

