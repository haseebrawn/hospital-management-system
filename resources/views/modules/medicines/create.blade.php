@extends('layouts.app')

@section('title', 'Create Medicine')

@section('content')
    <div class="card">
        <div class="card-title">Create Medicine</div>
        <div class="card-subtitle">Add a new item to the pharmacy inventory.</div>

        @include('modules.medicines._form', [
            'medicine' => null,
            'statusOptions' => $statusOptions,
            'action' => route('medicines.store'),
            'method' => 'POST',
        ])
    </div>
@endsection

