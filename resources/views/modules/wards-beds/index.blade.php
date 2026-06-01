@extends('layouts.app')

@section('title', 'Wards & Beds')

@section('content')
    <div class="card">
        <div class="card-title">Wards &amp; Beds</div>
        <div class="card-subtitle">Manage wards, beds, and bed allocations.</div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-top: 14px;">
            <a href="{{ route('wards.index') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Wards</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Create and manage wards.</div>
            </a>

            <a href="{{ route('beds.index') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Beds</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Create and manage beds.</div>
            </a>

            <a href="{{ route('allocations.index') }}"
                style="text-decoration:none; color:inherit; border:1px solid var(--border-color); border-radius:14px; padding:14px;">
                <div style="font-weight:800;">Allocations</div>
                <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">Assign, release, transfer beds.</div>
            </a>
        </div>
    </div>
@endsection
