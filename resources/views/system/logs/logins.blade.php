@extends('layouts.app')

@section('title', 'System — Login Logs')

@section('content')
    <div class="card">
        <div class="card-title">Login Logs</div>
        <div class="card-subtitle">Track login attempts.</div>

        <form method="GET" action="{{ route('system.logs.logins') }}" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            <div style="min-width:240px;">
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Email</div>
                <input name="email" value="{{ $email }}"
                    style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">Success</div>
                <select name="success"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                    <option value="" {{ $success === '' ? 'selected' : '' }}>All</option>
                    <option value="1" {{ (string) $success === '1' ? 'selected' : '' }}>Success</option>
                    <option value="0" {{ (string) $success === '0' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">From</div>
                <input type="date" name="from" value="{{ $from }}"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <div>
                <div style="font-size:12px; color: var(--text-muted); margin-bottom:6px;">To</div>
                <input type="date" name="to" value="{{ $to }}"
                    style="padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
            </div>
            <button type="submit"
                style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                Filter
            </button>
            <a href="{{ route('system.logs.logins') }}" style="font-size:13px; color: var(--primary); text-decoration:none;">
                Clear
            </a>
        </form>

        <div style="margin-top: 16px; overflow:auto;">
            <table class="dash-table" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>User</th>
                        <th>IP</th>
                        <th>Success</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td style="font-weight:700;">{{ $log->email }}</td>
                            <td>{{ optional($log->user)->name ?? '-' }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->success ? 'Yes' : 'No' }}</td>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:16px;">No login logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $logs->links() }}
        </div>
    </div>
@endsection

