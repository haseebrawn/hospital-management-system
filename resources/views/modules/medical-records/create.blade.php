@extends('layouts.app')

@section('title', 'Create Medical Record')

@section('content')
    <div class="card">
        <div class="card-title">Create Medical Record</div>
        <div class="card-subtitle">Add diagnosis, vitals, history, allergies, and doctor notes with linked appointment context when available.</div>

        <div style="margin-top:16px;">
            @include('modules.medical-records._form', [
                'medicalRecord' => $medicalRecord,
                'linkedAppointment' => $linkedAppointment,
                'patients' => $patients,
                'doctors' => $doctors,
                'appointments' => $appointments,
                'visitTypes' => $visitTypes,
                'action' => route('medical-records.store'),
            ])
        </div>
    </div>
@endsection
