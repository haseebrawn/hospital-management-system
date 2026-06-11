@extends('layouts.app')

@section('title', 'Edit Doctor Availability')

@section('content')
    <div class="card">
        <div class="card-title">Edit Doctor Availability</div>
        <div class="card-subtitle">Update a doctor appointment availability slot.</div>

        <div style="margin-top:16px;">
            @include('modules.doctor-availabilities._form', [
                'availability' => $doctorAvailability,
                'doctors' => $doctors,
                'dayOptions' => $dayOptions,
                'action' => route('doctor-availabilities.update', $doctorAvailability),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
