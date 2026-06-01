@extends('layouts.app')

@section('title', 'Create Staff')

@section('content')
    <div class="card">
        <div class="card-title">Create Staff Profile</div>
        <div class="card-subtitle">Create HR profile for an existing user.</div>

        @include('modules.staff._form', [
            'staff' => null,
            'users' => $users,
            'departments' => $departments,
            'statusOptions' => $statusOptions,
            'action' => route('staff.store'),
            'method' => 'POST',
        ])
    </div>
@endsection

