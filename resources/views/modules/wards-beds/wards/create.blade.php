@extends('layouts.app')

@section('title', 'Create Ward')

@section('content')
    <div class="card">
        <div class="card-title">Create Ward</div>
        <div class="card-subtitle">Add a new ward.</div>

        @include('modules.wards-beds.wards._form', [
            'ward' => null,
            'departments' => $departments,
            'action' => route('wards.store'),
            'method' => 'POST',
        ])
    </div>
@endsection

