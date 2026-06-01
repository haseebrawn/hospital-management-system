@extends('layouts.app')

@section('title', 'Medicine Details')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap: 12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">{{ $medicine->name }}</div>
                <div class="card-subtitle">Medicine profile</div>
            </div>
            <div style="display:flex; gap: 10px; flex-wrap:wrap;">
                <a href="{{ route('medicines.edit', $medicine) }}"
                    style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Edit
                </a>
                <form method="POST" action="{{ route('medicines.destroy', $medicine) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Delete this medicine?')"
                        style="padding:8px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color:#dc2626; cursor:pointer; font-size:13px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Stock</div>
                <div style="font-weight:600; margin-top:4px;">{{ $medicine->stock }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Price</div>
                <div style="font-weight:600; margin-top:4px;">{{ number_format((float) $medicine->price, 2) }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Expiry date</div>
                <div style="font-weight:600; margin-top:4px;">{{ $medicine->expiry_date ?? '-' }}</div>
            </div>
            <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px;">
                <div style="font-size:12px; color: var(--text-muted);">Status</div>
                <div style="font-weight:600; margin-top:4px; text-transform:capitalize;">{{ $medicine->status }}</div>
            </div>
        </div>

        <div style="margin-top: 14px; padding:12px; border:1px solid var(--border-color); border-radius:14px;">
            <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Description</div>
            <div style="white-space:pre-wrap;">{{ $medicine->description ?: '-' }}</div>
        </div>

        <div style="margin-top: 16px;">
            <a href="{{ route('medicines.index') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                ← Back to medicines
            </a>
        </div>
    </div>
@endsection

