@extends('layouts.app')

@section('title', 'Edit Bed')

@section('content')
    <div class="card">
        <div class="card-title">Edit Bed</div>
        <div class="card-subtitle">Update ward, number, and status.</div>

        @include('modules.wards-beds.beds._form', [
            'bed' => $bed,
            'wards' => $wards,
            'statusOptions' => $statusOptions,
            'action' => route('beds.update', $bed),
            'method' => 'PUT',
        ])
    </div>
@endsection

