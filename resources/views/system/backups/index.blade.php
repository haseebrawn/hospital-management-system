@extends('layouts.app')

@section('title', 'System — Backups')

@section('content')
    <div class="card">
        <div class="card-title">Backups</div>
        <div class="card-subtitle">Create and download database backups.</div>

        <form method="POST" action="{{ route('system.backups.store') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            @csrf
            <div style="flex:1; min-width:260px;">
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Notes (optional)</div>
                <input name="notes" value="{{ old('notes') }}"
                    style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                Create Backup
            </button>
        </form>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Filename</th>
                        <th>Created By</th>
                        <th>Size</th>
                        <th>Notes</th>
                        <th>Created</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $b)
                        <tr>
                            <td>{{ $b->id }}</td>
                            <td style="font-weight:700;">{{ $b->filename }}</td>
                            <td>{{ optional($b->creator)->name ?? '-' }}</td>
                            <td>{{ $b->filesize ? number_format($b->filesize) : '-' }}</td>
                            <td>{{ $b->notes ?: '-' }}</td>
                            <td>{{ optional($b->created_at)->format('Y-m-d H:i') }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('system.backups.download', $b) }}"
                                    style="font-size:13px; color: var(--primary); text-decoration:none;">
                                    Download
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 16px;">No backups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $backups->links() }}
        </div>
    </div>
@endsection

