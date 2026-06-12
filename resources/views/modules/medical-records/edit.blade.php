@extends('layouts.app')

@section('title', 'Edit Medical Record')

@section('content')
    <div class="card">
        <div class="card-title">Edit Medical Record</div>
        <div class="card-subtitle">Update clinical notes and visit details.</div>

        <div style="margin-top:16px;">
            @include('modules.medical-records._form', [
                'medicalRecord' => $medicalRecord,
                'patients' => $patients,
                'doctors' => $doctors,
                'appointments' => $appointments,
                'visitTypes' => $visitTypes,
                'action' => route('medical-records.update', $medicalRecord),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
