@extends('layouts.app')

@section('title', 'Edit Staff')

@section('content')
    <div class="card">
        <div class="card-title">Edit Staff Profile</div>
        <div class="card-subtitle">Update staff information.</div>

        @include('modules.staff._form', [
            'staff' => $staff,
            'users' => null,
            'departments' => $departments,
            'statusOptions' => $statusOptions,
            'action' => route('staff.update', $staff),
            'method' => 'PUT',
        ])
    </div>
@endsection

