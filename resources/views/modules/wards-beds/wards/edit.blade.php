@extends('layouts.app')

@section('title', 'Edit Ward')

@section('content')
    <div class="card">
        <div class="card-title">Edit Ward</div>
        <div class="card-subtitle">Update ward information.</div>

        @include('modules.wards-beds.wards._form', [
            'ward' => $ward,
            'departments' => $departments,
            'action' => route('wards.update', $ward),
            'method' => 'PUT',
        ])
    </div>
@endsection

