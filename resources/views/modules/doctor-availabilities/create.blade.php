@extends('layouts.app')

@section('title', 'Add Doctor Availability')

@section('content')
    <div class="card">
        <div class="card-title">Add Doctor Availability</div>
        <div class="card-subtitle">Define the weekly time slot when a doctor is available for appointments.</div>

        <div style="margin-top:16px;">
            @include('modules.doctor-availabilities._form', [
                'doctors' => $doctors,
                'dayOptions' => $dayOptions,
                'action' => route('doctor-availabilities.store'),
            ])
        </div>
    </div>
@endsection
