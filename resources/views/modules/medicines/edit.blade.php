@extends('layouts.app')

@section('title', 'Edit Medicine')

@section('content')
    <div class="card">
        <div class="card-title">Edit Medicine</div>
        <div class="card-subtitle">Update stock, price, and status.</div>

        @include('modules.medicines._form', [
            'medicine' => $medicine,
            'statusOptions' => $statusOptions,
            'action' => route('medicines.update', $medicine),
            'method' => 'PUT',
        ])
    </div>
@endsection

